
function fetchElementById(id) 
{ 
	if (document.getElementById) {
		var return_var = document.getElementById(id); 
	} else if (document.all) {
		var return_var = document.all[id]; 
	} else if (document.layers) { 
		var return_var = document.layers[id]; 
	} else {
		alert(".را نميتوان وارد کرد'" + id + "'عنصر شماره");
	}
	return return_var; 
}

function new_banner_input(upload_type) 
{
	var block_id = fetchElementById("more_banner_inputs");

		var file_id  = ("banner-" + index_amf_total);
		var file_div = document.createElement("div");
		
		file_div.setAttribute("id", file_id);
		file_div.innerHTML += ''+_Banner_URL+'<input name="banner['+index_amf_total+'][src]"  size="76" type="text" /> <br /> '+Banner_Link+' &nbsp;&nbsp;<input name="banner['+index_amf_total+'][href]" size="50" type="text" /> '+Number_of_views+'<input name="banner['+index_amf_total+'][display]" size="4" type="text" /> '+_Percent+' <br /> '+Banner_title+' &nbsp;&nbsp;<input name="banner['+index_amf_total+'][title]" size="50" type="text" /><br /> '+Banner_Description+' &nbsp;&nbsp;<input name="banner['+index_amf_total+'][description]" size="50" type="text" />';

		file_div.innerHTML += "<input type=\"button\" class=\"button1\" onclick=\"javascript:remove_banner_input('" + file_id + "');\" style=\"height: 19px;\" value=\""+_Remove+"\" /> <p />";
		
		index_amf_total++;
		
		block_id.appendChild(file_div);
	return true;
}
	
function remove_banner_input(div)
{
	var block_id = fetchElementById("more_banner_inputs");
	var file_div = fetchElementById(div);

	block_id.removeChild(file_div);

	index_amf_total--;

	return true;
}