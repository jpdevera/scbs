/* REPORT HEADER STYLE */
table {
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;	
}

.table-report-header{
	margin-bottom:25px;
}
.table-report-header thead tr .top-header-title{
	text-transform:uppercase;
	font-weight:bold;
	font-size:15px;
}
.table-report-header thead tr .header-title{
	font-size:18px;
	font-weight:bold;
	text-align:center;
	text-transform:uppercase; 
	padding:10px 0 7px;
}
.table-report-header thead tr .subheader-title,
.subheader-title{	
	text-align:center;
	font-size:16px;
}

.table-report{
	width:100%;
	font-size:12px;
	margin-top:10px;
}
.table-report thead tr{
	background:#44B7D5;
}
.table-report thead tr th{
	color:#fff;
	padding:10px 5px;
	font-size:9px;
	text-transform:uppercase;
}
.table-report tbody tr td{
	padding:8px 5px;
}
.table-report tbody tr:nth-child(odd){
	background:#FAFAFA;
}


/* ADVANCED TABLE STYLE */
.table-report-advanced{
	border:none;
	border-bottom:1px solid #DAE3E5;	
	width:100%;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}
.table-report-advanced th, .table-report-advanced td{
	border-radius:0;
}
.table-report-advanced thead tr.row-header th{
	background:#f6f6f6;
	padding:10px;
	font-size:7pt;
	text-transform:uppercase;
	text-align:center;
	border-bottom:1px solid #DAE3E5;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}
.table-report-advanced thead tr.row-header-black th,
.table-report-advanced thead tr.row-header-black td {
	background:#ededed;
	padding:10px;
	font-size:7pt;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
	text-transform:uppercase;
	text-align:center;
	color : black;
	border-bottom:1px solid #DAE3E5;
}
.table-report-advanced thead tr.row-header th.col-group-header{
	background:#21AD88;
	color:#fff;
}
.table-report-advanced thead tr.row-header th.col-group-header-orange{
	background:#E78549;
	color:#fff;
}
.table-report-advanced thead tr.row-header th.col-group-header-blue{
	background:#5AB7B4;
	color:#fff;
}

.table-report-advanced thead tr.row-subheader th{
	background:#F6FBFC;
	padding:10px;
	font-size:7pt;
	text-align:center;
	border:none;
	border-bottom:1px solid #DAE3E5;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}
.table-report-advanced thead tr td{
	background:#f3f3f3;
	padding:5px;
	font-size:7pt;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
	border:none;
}
.table-report-advanced thead tr.row-subheader th.col-subheader{
	background:#E8F0F2;
}
.table-report-advanced tbody{
	border-left:1px solid #eee;
}
.table-report-advanced tbody tr td{
	font-size:7pt;
	padding:10px;
	vertical-align:top;
	border:none;
	border-left:1px solid #eee;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}

.table-report-advanced tbody tr td.row-group-header{
	padding:10px;
	text-transform:uppercase;
	font-weight:bold;
	border:none;
	border-left:1px solid #eee;
	background:#fbfbfb;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}
.table-report-advanced tbody tr td.row-group-subheader{
	padding:10px 25px;
	border:none;
	border-left:1px solid #eee;
	background:#fbfbfb;
	font-weight:bold;
	font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
}
.table-report-advanced tbody tr td.row-group-indent{
	padding:10px 40px;
}
.table-report-advanced tbody tr:nth-child(odd){
	background:#fefefe;
}
.table-report-advanced tbody tr:nth-child(even){
	background:#F6F6F6;
}
.table-report-advanced tfoot tr{
	background:#fff;
}
.table-report-advanced tfoot tr td{
	border-top:1px solid #DAE3E5;
	padding:10px;
	font-size:14px;
}
.footer{
	margin-top:20px;
}
.footer .note{
	font-size:14px;
}
.footer .section-title{
	font-weight:bold;
	font-size:15px;
	margin:30px 0;
}

.report-signatory-table{
  margin:30px 0 20px;
}
.report-signatory-table tbody tr th{
	font-size:16px;
	vertical-align:top;
	padding:10px 0;
}
.report-signatory-table tbody tr td{
	font-size:15px;
	padding:10px 0;
	vertical-align:top;
}
.report-signatory-table tbody tr td.label{
	border-top:1px solid #000;
	font-weight:bold;
	font-size:14px;
	text-transform:uppercase;
	padding:7px 0;
}

/* PORTRAIT ORIENTATION STYLE */
	
	/* REPORT HEADER */
	.portrait .table-report-header thead tr .top-header-title{
		font-size:9.5px;
	}
	.portrait .table-report-header thead tr .header-title{	
		font-size:12px;
	}
	.portrait .table-report-header thead tr .subheader-title,
	.portrait .subheader-title{
		font-size:11px;
	}
	
	/* TABLE LAYOUT */
	.portrait .table-report-advanced thead tr.row-header th,
	.portrait .table-report-advanced thead tr.row-subheader th,
	.portrait .table-report-advanced thead tr.row-subheader th.col-group-header,
	.portrait .table-report-advanced thead tr.row-subheader th.col-group-header-orange{
		font-size:9.5px;
	}
	.portrait .table-report-advanced thead tr td{
		font-size:8px;
	}
	.portrait .table-report-advanced tbody tr td,
	.portrait .table-report-advanced tfoot tr td{
		font-size:9.5px;		
	}
	/* FOOTER */
	.portrait .footer .note{
		font-size:10px;
		line-height:17px;
	}
	.portrait .footer .section-title{
		font-size:10px;
	}
	.portrait .report-signatory-table tbody tr td.label{
		font-size:9px;
	}
	.portrait .report-signatory-table tbody tr th{
		font-size:10.5px;
	}
	.portrait .report-signatory-table tbody tr td{
		font-size:9.5px;
	}

/* END PORTRAIT ORIENTATION STYLE */

/* EXCEL FILE TYPE STYLE */
*{
	font-size:12px!important;
}
/* END EXCEL FILE TYPE STYLE */

.right-align{ text-align:right; }
.left-align{ text-align:left; }
.center-align{ text-align:center; }
.empty-data{
	text-align:center;
	padding:25px;
	font-size:12px;
	text-transform:uppercase;
	border-right:none!important;
}


.logo{
	height: 10%;
	width: 13%;
}
.all-table{
	font-size: 12px !important;
}

.basic-border{
	border-collapse: collapse;
	border: 1px solid black;
}

.basic-border thead tr th{
	border-collapse: collapse;
	border: 1px solid black;
}

.basic-border tbody tr td{
	border-collapse: collapse;
	border: 1px solid black;
}

.subheading{
	background:#d9d9d9;

}

.heading{
	background:#b3b3b3;
	font-weight: bold;
}

.p-l{
	padding-left:15px;
}

.border-table-elem {
	border-collapse: collapse;
	border: 1px solid black;
}