"""Régression Apache CGI locale (MAMP), sans compte ni accès de production."""

import json
from pathlib import Path
import socket
import subprocess
import tempfile
import time
import urllib.request

REPO = Path(__file__).resolve().parents[1]
MAMP = Path('/Applications/MAMP')
FORWARDING = (
    b'RewriteCond %{HTTP:Authorization} .\n'
    b'RewriteRule ^ - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n'
)
PROBE = """#!/bin/sh
printf 'Content-Type: application/json\\r\\n\\r\\n'
if [ -n "$HTTP_AUTHORIZATION$REDIRECT_HTTP_AUTHORIZATION" ]; then
    printf '{"authorization_received":true}'
else
    printf '{"authorization_received":false}'
fi
"""

with tempfile.TemporaryDirectory(prefix='bdovore-apache-auth-') as directory:
    root = Path(directory)
    sample = (REPO / 'public/.htaccess.sample').read_bytes()
    assert FORWARDING in sample, 'Les directives de transmission sont absentes.'
    for name in ['baseline', 'fixed']:
        target = root / name
        target.mkdir()
        htaccess = sample.replace(FORWARDING, b'') if name == 'baseline' else sample
        (target / '.htaccess').write_bytes(htaccess)
        script = target / 'index.php'
        script.write_text(PROBE)
        script.chmod(0o700)

    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]

    modules = MAMP / 'Library/modules'
    loads = '\n'.join(
        f'LoadModule {name}_module "{modules / ("mod_" + name + ".so")}"'
        for name in ['authz_core', 'env', 'unixd', 'alias', 'rewrite', 'mime', 'cgid']
    )
    config = root / 'httpd.conf'
    config.write_text(f'''ServerRoot "{MAMP}/Library"
{loads}
ServerName localhost
Listen 127.0.0.1:{port}
PidFile "{root}/httpd.pid"
ErrorLog "{root}/error.log"
TypesConfig {MAMP}/conf/apache/mime.types
ScriptSock "{root}/cgi.sock"
DocumentRoot "{root}"
<Directory "{root}">
Options +FollowSymLinks +ExecCGI
AllowOverride All
Require all granted
AddHandler cgi-script .php
</Directory>
''')
    apache = subprocess.Popen(
        [str(MAMP / 'Library/bin/httpd'), '-X', '-f', str(config)],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.PIPE,
    )
    try:
        for attempt in range(30):
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=0.2):
                    break
            except OSError:
                if apache.poll() is not None:
                    raise RuntimeError(apache.stderr.read().decode())
                time.sleep(0.1)
        else:
            raise RuntimeError('Apache ne démarre pas dans le délai prévu.')

        results = {}
        for name in ['baseline', 'fixed']:
            request = urllib.request.Request(
                f'http://127.0.0.1:{port}/{name}/apiauth/probe',
                headers={'Authorization': 'Bearer bdo2_' + '0' * 64},
            )
            try:
                with urllib.request.urlopen(request, timeout=5) as response:
                    results[name] = json.load(response)
            except Exception:
                print((root / 'error.log').read_text())
                raise
        print(json.dumps(results))
        assert results['baseline']['authorization_received'] is False
        assert results['fixed']['authorization_received'] is True
        print('Apache CGI : perte reproduite avant correction, transmission validée après correction.')
    finally:
        apache.terminate()
        try:
            apache.wait(timeout=5)
        except subprocess.TimeoutExpired:
            apache.kill()
            apache.wait()
