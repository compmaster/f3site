function Calendar(d,t,id)
{
 this.create=function()
 {
  if(d(id).value==0 || d(id).value=='')
  {
   var c=new Date();
  }
  else
  {
   var a=split(d);
	 var t=split(a[0]);
   if(a[1]) {
	  this.c=new Date(t[0],t[1],t[2])
	 }
	 else {
	  var t2=split(a[1]); this.c=new Date(t[0],t[1],t[2],t[3],t[4])
	 }
	 var h='<table><tbody><tr>';
	 //z: col, y: row
	 for(var i=0,var y=1,var z=0;i<42;i++)
	 {
	  h+='<td id="'+id+z+'_'+y+'" onclick="'+this+'.set()">&nbsp;</td>';
		if(y==7) { y++; } else { y=1 }
	 }
	 h+='</tbody></table>';
  }
 }
 var 
}