function openSidebar(){document.getElementById('sidebar')?.classList.add('open')}
function closeSidebar(){document.getElementById('sidebar')?.classList.remove('open')}
function toggleConnection(button){button.textContent=button.textContent==='Connect'?'Disconnect':'Connect'}
function downloadCsv(){
  const rows=[['Name','Type','Timestamp'],['Sarah Mthembu','in',new Date().toISOString()],['John Adams','out',new Date().toISOString()]];
  const csv=rows.map(row=>row.join(',')).join('\n');
  const blob=new Blob([csv],{type:'text/csv'});
  const url=URL.createObjectURL(blob);
  const link=document.createElement('a');
  link.href=url;link.download='attendance-log.csv';link.click();URL.revokeObjectURL(url);
}
