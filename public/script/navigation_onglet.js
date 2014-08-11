
function ChClassName(obj,a)
{
	var nomclass = obj.className;
	var extension=nomclass.substring(nomclass.lastIndexOf("_"));
	var dimnomclass=nomclass.substring(0,nomclass.lastIndexOf("_"));
	if (a == "")
	{
		if (extension == '_out')
		{
			obj.className=dimnomclass+'_on';
		}
		else
		{
			obj.className=dimnomclass+'_out';
		}
	}
	else
	{
					obj.className=dimnomclass + a;
	}
}


function VoirArboOnglet(nom_div)
{
	if (tab_arbo_div[nom_div])
	{
		var tab_arbo=tab_arbo_div[nom_div].split(";");
		for (j=0;j<tab_arbo.length;j++)
		{
			showPref(tab_arbo[j]);
		}
	}
}


function showPref(id_div)
{
	var id_pack_div = tab_div[id_div]["id_pack"];
	var long_tab_div = tab_assoc_div[id_pack_div].length;

	for (i=0;i<long_tab_div;i++)
	{
		var id_div_ch = tab_assoc_div[id_pack_div][i];
		
		var nom_td="cfg_td_"+id_div_ch.toString();
		
		var nomclass = getObj(tab_div[id_div_ch]["cfg_td_id"]).className;
		var dimnomclass=nomclass.substring(0,nomclass.lastIndexOf("onglet"));
		if (id_div_ch==id_div)
		{
			getObj(tab_div[id_div_ch]["cfg_td_id"]).className = dimnomclass+"onglet_line_trl_on";
			getObj(tab_div[id_div_ch]["div_name"]).style.display = 'block';
		}
		else
		{
			getObj(tab_div[id_div_ch]["cfg_td_id"]).className = dimnomclass+"onglet_line_trbl_out";
			getObj(tab_div[id_div_ch]["div_name"]).style.display = 'none';
		}
	}
}
