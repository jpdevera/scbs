(function(c){"function"===typeof define&&define.amd?define(["jquery","datatables.net"],function(a){return c(a,window,document)}):"object"===typeof exports?module.exports=function(a,d){a||(a=window);if(!d||!d.fn.dataTable)d=require("datatables.net")(a,d).$;return c(d,a,a.document)}:c(jQuery,window,document)})(function(c,a,d){var g=c.fn.dataTable;c.extend(!0,g.defaults,{dom:"<'row m-n dataTable-filter'<'col s9 p-n'l><'col s3 p-n'f>><'row dt-table m-n'<'col s12 p-n'tr>><'row dataTable-footer m-n'<'col s4 p-n'i><'col s8 p-n'p>>",renderer:"material"});c.extend(g.ext.classes,{sWrapper:"dataTables_wrapper form-inline dt-material",sFilterInput:"form-control input-sm",sLengthSelect:"selectize form-control input-sm",sProcessing:"dataTables_processing panel panel-default"});g.ext.renderer.pageButton.material=function(a,h,r,s,i,n){var o=new g.Api(a),l=a.oLanguage.oPaginate,t=a.oLanguage.oAria.paginate||{},f,e,p=0,btn=[],q=function(d,g){var m,h,j,b,k=function(a){a.preventDefault();!c(a.currentTarget).hasClass("disabled")&&o.page()!=a.data.action&&o.page(a.data.action).draw("page")};m=0;for(h=g.length;m<h;m++)
if(b=g[m],c.isArray(b))
{q(d,b)}
else{f="";j=!1;switch(b){case "ellipsis":f="&#x2026;";e="disabled";break;case "first":f=l.sFirst;e=b+(0<i?"":" disabled");break;case "previous":f=l.sPrevious;e=b+(0<i?"":" disabled");break;case "next":f=l.sNext;e=b+(i<n-1?"":" disabled");break;case "last":f=l.sLast;e=b+(i<n-1?"":" disabled");break;default:f=b+1,e="",j=i===b}
j&&(e+=" dataTable-paginate-active");f&&(j=c("<button>",{"class":"dataTable-paginate-button "+e,id:0===r&&"string"===typeof b?a.sTableId+"_"+b:null,"aria-controls":a.sTableId,"aria-label":t[b],"data-dt-idx":p,tabindex:a.iTabIndex,disabled:-1!==e.indexOf("disabled")}).html(f).appendTo(d),a.oApi._fnBindAction(j,{action:b},k),p++)}},k;try{k=c(h).find(d.activeElement).data("dt-idx")}catch(u){}
q(c(h).empty().html('<div class="pagination"/>').children(),s);if(a.oInit.jump_to_page)
{var opt='',pages=o.page.info().pages,i=0;if(pages.length!==0)
{for(;i<pages;i++)
{var sel=(o.page()==i)?'selected':'';opt+='<option value="'+i+'" '+sel+'>&nbsp;&nbsp;'+(i+1)+'&nbsp;&nbsp;</option>'}}
c(h).find('.pagination').find('button.dataTable-paginate-button').wrapAll('<div class="col s10"></div>')
c(h).find('.pagination').append(`<div class="col s2 p-n m-n dt-select-div">
	        		<select class="form-control input-sm dt-select selectize-dt">
	        			`+opt+`
	        		</select>
	        	</div>
	        	`);if(c(h).find('.selectize-dt').length!==0&&typeof(c.fn.selectize)!=='undefined')
{c(h).find('.selectize-dt').selectize({dropdownDirection:'up',dropdownParent:null})}
c(h).find('.pagination').find('.dt-select').on('change',function(e)
{e.stopImmediatePropagation();if(c(this).val()!='')
{var act=parseInt(c(this).val());!c(e.currentTarget).hasClass("disabled")&&o.page()!=act&&o.page(act).draw("page")}})}
k&&c(h).find("[data-dt-idx="+k+"]").focus()};return g})