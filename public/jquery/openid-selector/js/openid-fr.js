/*
	Simple OpenID Plugin
	http://code.google.com/p/openid-selector/
	
	This code is licensed under the New BSD License.
*/

var providers_large = {/*
	facebook : {
		name : 'Facebook',
		url : 'facebook',
		place:1
	},
	twitter : {
		name : 'Twitter',
		url : 'twitter',
		place:2
	},
	google : {
		name : 'Google',
		url : 'https://www.google.com/accounts/o8/id',
		place:3
	},
	yahoo : {
		name : 'Yahoo',
		url : 'http://me.yahoo.com/',
		place:4
	},
	aol : {
		name : 'AOL',
		label : 'Indiquez votre pseudonyme AOL',
		url : 'http://openid.aol.com/{username}',
		place:5
	},
	myopenid : {
		name : 'MyOpenID',
		label : 'Enter your MyOpenID username.',
		url : 'http://{username}.myopenid.com/',
		place:6
	},
	openid : {
		name : 'OpenID',
		label : 'Enter your OpenID.',
		url : null,
		place:7
	}*/
};
var providers_small = {
		google : {
			name : 'Google',
			url : 'https://www.google.com/accounts/o8/id',
			place:1
		},
		yahoo : {
			name : 'Yahoo',
			url : 'https://me.yahoo.com/',
			place:2
		},
		aol : {
			name : 'AOL',
			label : 'Indiquez votre pseudonyme AOL',
			url : 'http://openid.aol.com/{username}',
			place:3
		}/*,
		myopenid : {
		name : 'MyOpenID',
		label : 'Enter your MyOpenID username.',
		url : 'http://{username}.myopenid.com/',
		place:4
	}*/,
	openid : {
		name : 'OpenID',
		label : 'Renseignez votre provider OpenId.',
		url : null,
		place:5
	}/*,

	livejournal : {
		name : 'LiveJournal',
		label : 'Enter your Livejournal username.',
		url : 'http://{username}.livejournal.com/',
		place:6
	}, 
	 wordpress : {
		name : 'Wordpress',
		label : 'Enter your Wordpress.com username.',
		url : 'http://{username}.wordpress.com/',
		place:7
	},
	blogger : {
		name : 'Blogger',
		label : 'Your Blogger account',
		url : 'http://{username}.blogspot.com/',
		place:8
	},
	verisign : {
		name : 'Verisign',
		label : 'Your Verisign username',
		url : 'http://{username}.pip.verisignlabs.com/',
		place:9
	},
	claimid : {
		name : 'ClaimID',
		label : 'Your ClaimID username',
		url : 'http://claimid.com/{username}',
		place:10
	},
	google_profile : {
		name : 'Google Profile',
		label : 'Enter your Google Profile username',
		url : 'http://www.google.com/profiles/{username}',
		place:12
	}*/,
	facebook : {
		name : 'Facebook',
		url : 'facebook',
		place:13
	},
	twitter : {
		name : 'Twitter',
		url : 'twitter',
		place:14
	}/*,
	flickr: {
		name: 'Flickr',        
		label: 'Indiquez votre pseudonyme Flickr.',
		url: 'http://flickr.com/{username}/',
		place : 15
	}*/,
	Orange : {
		name : 'Orange',
		url : 'http://openid.orange.fr',
		place : 16
	}/*, 
	 technorati: {
		name: 'Technorati',
		label: 'Enter your Technorati username.',
		url: 'http://technorati.com/people/technorati/{username}/'
	},
	vidoop: {
		name: 'Vidoop',
		label: 'Your Vidoop username',
		url: 'http://{username}.myvidoop.com/'
	},
	launchpad: {
		name: 'Launchpad',
		label: 'Your Launchpad username',
		url: 'https://launchpad.net/~{username}'
	} */
};

openid.locale = 'fr';
openid.sprite = 'fr'; // reused in german& japan localization
openid.demo_text = 'Mode de démo client. Normalement, l\'OpenID suivant aurait été soumis :';
openid.signin_text = 'Connexion';
openid.image_title = 'Connectez-vous avec {provider}';
openid.img_path = '/images/';