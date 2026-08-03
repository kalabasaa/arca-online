<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
body{background:#0A0A16;color:#fff;min-height:100vh;}

header{
    width:100%;height:60px;background:#1c1c29;
    display:flex;align-items:center;justify-content:space-between;padding:0 24px;
    border-bottom:1px solid rgba(255,255,255,0.06);
    position:sticky;top:0;z-index:50;
}
header .logo img{width:70px;height:auto;}
header nav{display:flex;gap:6px;}
header nav a{
    color:#aaa;text-decoration:none;font-size:0.85rem;
    padding:6px 14px;border-radius:6px;transition:0.2s;
}
header nav a:hover{color:#fff;background:rgba(255,255,255,0.06);}
header nav a.active{color:#00bcd4;background:rgba(0,188,212,0.08);}
header .logout button{
    background:#e74c3c;border:none;color:#fff;
    padding:6px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;
    font-weight:bold;transition:0.2s;
}
header .logout button:hover{background:#c0392b;}

main{padding:28px 32px;max-width:1200px;margin:0 auto;}

.page-header{margin-bottom:24px;}
.page-header h1{font-size:1.5rem;font-weight:700;}
.page-header .page-sub{color:#666;font-size:0.85rem;margin-top:4px;}

.stats-row{
    display:grid;grid-template-columns:repeat(4,1fr);gap:14px;
    margin-bottom:24px;
}
.stat-card{
    background:#1f1f2e;border-radius:10px;padding:18px 20px;
    border:1px solid rgba(255,255,255,0.06);
}
.stat-label{font-size:0.75rem;color:#666;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;}
.stat-value{font-size:1.5rem;font-weight:700;color:#00bcd4;}

.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;align-items:center;}
.filters input[type="date"],
.filters select{
    background:#1f1f2e;border:1px solid rgba(255,255,255,0.1);
    color:#fff;padding:7px 12px;border-radius:7px;font-size:0.85rem;
    cursor:pointer;
}
.filters input[type="date"]:focus,
.filters select:focus{outline:none;border-color:#00bcd4;}

.sort-buttons{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;}
.sort-buttons a{
    text-decoration:none;padding:6px 14px;
    background:#1f1f2e;color:#aaa;border-radius:6px;font-size:0.83rem;
    border:1px solid rgba(255,255,255,0.07);transition:0.2s;
}
.sort-buttons a:hover{color:#fff;border-color:rgba(255,255,255,0.2);}
.sort-buttons a.active{background:rgba(0,188,212,0.1);color:#00bcd4;border-color:rgba(0,188,212,0.3);}

.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:separate;border-spacing:0 6px;min-width:600px;}
th{text-align:left;font-weight:500;font-size:0.78rem;text-transform:uppercase;
   letter-spacing:0.06em;color:#555;padding:6px 16px;}
td{padding:12px 16px;background:#1f1f2e;font-size:0.88rem;color:#ddd;
   border-top:1px solid rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.04);}
td:first-child{border-radius:8px 0 0 8px;border-left:1px solid rgba(255,255,255,0.04);}
td:last-child{border-radius:0 8px 8px 0;border-right:1px solid rgba(255,255,255,0.04);}
tbody tr:hover td{background:#252535;}
td.empty{text-align:center;color:#555;padding:28px;}

.badge{
    display:inline-block;padding:3px 9px;border-radius:99px;
    font-size:0.72rem;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;
}
.badge-green {background:rgba(39,174,96,0.15);color:#27ae60;}
.badge-red   {background:rgba(231,76,60,0.15);color:#e74c3c;}
.badge-blue  {background:rgba(0,188,212,0.12);color:#00bcd4;}
.badge-yellow{background:rgba(241,196,15,0.12);color:#f1c40f;}
.badge-gray  {background:rgba(127,140,141,0.15);color:#95a5a6;}

.btn-danger{
    background:#e74c3c;border:none;color:#fff;
    padding:5px 12px;border-radius:6px;cursor:pointer;
    font-size:0.8rem;font-weight:bold;transition:0.2s;
}
.btn-danger:hover{background:#c0392b;}

.delete-confirm{
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
    background:#1c1c29;padding:24px 32px;border-radius:10px;
    box-shadow:0 0 40px rgba(0,0,0,0.6);text-align:center;z-index:200;display:none;
    border:1px solid rgba(255,255,255,0.08);min-width:280px;
}
.delete-confirm p{margin-bottom:20px;font-size:0.95rem;color:#fff;}
.delete-confirm button{
    padding:8px 18px;margin:0 6px;border:none;
    border-radius:6px;cursor:pointer;font-weight:bold;font-size:0.85rem;transition:0.2s;
}
.confirm-yes{background:#e74c3c;color:#fff;}
.confirm-yes:hover{background:#c0392b;}
.confirm-no{background:#2c2c3e;color:#aaa;border:1px solid rgba(255,255,255,0.1);}
.confirm-no:hover{color:#fff;}

.overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,0.5);z-index:100;backdrop-filter:blur(2px);
}

.pagination{display:flex;gap:6px;margin-top:18px;align-items:center;flex-wrap:wrap;}
.pagination a,.pagination span{
    padding:6px 12px;border-radius:6px;font-size:0.83rem;text-decoration:none;
    background:#1f1f2e;color:#aaa;border:1px solid rgba(255,255,255,0.07);transition:0.2s;
}
.pagination a:hover{color:#fff;border-color:rgba(255,255,255,0.2);}
.pagination span.current{background:rgba(0,188,212,0.1);color:#00bcd4;border-color:rgba(0,188,212,0.3);}
.pagination .dots{background:none;border:none;color:#555;}

@media(max-width:900px){
    main{padding:18px 16px;}
    .stats-row{grid-template-columns:1fr 1fr;}
}
@media(max-width:500px){
    .stats-row{grid-template-columns:1fr;}
    header nav a span{display:none;}
}
</style>