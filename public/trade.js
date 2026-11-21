'use strict';
const $ = id => document.getElementById(id);
const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const money = value => new Intl.NumberFormat('ru-RU', {style:'currency',currency:'RUB',maximumFractionDigits:2}).format(Number(value));
const number = value => new Intl.NumberFormat('ru-RU', {maximumFractionDigits:3}).format(Number(value));
const today = new Intl.DateTimeFormat('sv-SE', {timeZone:'Europe/Kaliningrad'}).format(new Date());
const state = {page:'overview', products:[],departments:[],sales:[], editing:null, ready:false, pendingDelete:null};
const pages = {
 overview:['Обзор торговли','Продажи, отделы и выполнение плана в одном месте.','Обзор'],
 products:['Каталог товаров','Товары, единицы измерения и актуальные розничные цены.','Товары','Добавить товар'],
 departments:['Отделы','Заведующие, контакты и дневные планы реализации.','Отделы','Добавить отдел'],
 sales:['Продажи','Каждая продажа связана с одним отделом и одним товаром.','Продажи','Добавить продажу'],
 reports:['Отчёты','Анализ выполнения плана и спроса на товары.','Отчёты']
};
const fields = {
 products:[['sku','Артикул','text',50],['name','Название товара','text',255],['unit','Единица измерения','text',30],['retail_price','Розничная цена, ₽','money']],
 departments:[['code','Код отдела','text',30],['name','Название отдела','text',255],['manager_name','Ф. И. О. заведующего','text',255],['phone','Телефон','tel',30],['daily_plan','Дневной план, ₽','money']],
 sales:[['department_id','Отдел','department'],['product_id','Товар','product'],['sale_date','Дата продажи','date'],['quantity','Количество','quantity']]
};
const fieldNames = Object.fromEntries(Object.values(fields).flat().map(f=>[f[0],f[1]]));
async function api(path, options = {}) {
 const response = await fetch('/api/'+path, {...options,headers:{Accept:'application/json','Content-Type':'application/json',...options.headers}});
 if(response.status===204) return null;
 const json = await response.json().catch(()=>({message:'Сервер вернул неожиданный ответ.'}));
 if(!response.ok) {
  const error = new Error(json.message || 'Не удалось выполнить запрос.');
  error.details = json.errors; error.status = response.status; throw error;
 }
 return json;
}
function friendlyError(error) {
 if(error.details) return Object.keys(error.details).map(key=>{
  const name = fieldNames[key.split('.').pop()] || key;
  return `${name}: проверьте значение, допустимый диапазон и уникальность.`;
 }).join(' ');
 return error.status===500 ? 'Ошибка сервера. Проверьте, что MySQL запущен, и повторите попытку.' : error.message;
}
function notify(message, error = false) { $('notice').textContent=message; $('notice').className=error?'error':''; $('notice').hidden=false; }
async function all(entity) {
 let list=[], page=1;
 while(true) { const result=await api(entity+'?page='+page); list.push(...result.data); if(!result.links?.next) return list; page++; }
}
async function refreshData() {
 const [products,departments,sales] = await Promise.all(['products','departments','sales'].map(all));
 Object.assign(state,{products,departments,sales,ready:true});
 $('connection').textContent='База подключена';
}
const product = id => state.products.find(p=>p.id===Number(id));
const department = id => state.departments.find(d=>d.id===Number(id));
function dateLabel(value) { return String(value).slice(0,10).split('-').reverse().join('.'); }
function empty(title, text) { return `<div class="empty"><strong>${esc(title)}</strong>${esc(text)}</div>`; }
function table(headers,rows) { return `<table><thead><tr>${headers.map(h=>`<th>${esc(h)}</th>`).join('')}</tr></thead><tbody>${rows.map(row=>`<tr>${row.map(c=>`<td>${c}</td>`).join('')}</tr>`).join('')}</tbody></table>`; }
function saleRow(s) { return [esc(dateLabel(s.sale_date)),`<span class="cell-main">${esc(product(s.product_id)?.name||'Товар #'+s.product_id)}</span><span class="secondary-text">${esc(product(s.product_id)?.sku)}</span>`,esc(department(s.department_id)?.name),esc(number(s.quantity))+' '+esc(product(s.product_id)?.unit),esc(money(s.unit_price)),esc(money(Math.round(Number(s.quantity)*Number(s.unit_price)*100)/100))]; }
function actions(row) { return `<div class="row-actions"><button type="button" data-edit="${row.id}" aria-label="Изменить запись ${row.id}">Изменить</button><button type="button" class="delete" data-delete="${row.id}" aria-label="Удалить запись ${row.id}">Удалить</button></div>`; }
function renderRecords() {
 const entity=state.page, query=$('search').value.trim().toLocaleLowerCase('ru');
 const records=state[entity].filter(row=>{
  const joined=entity==='sales'? {...row,product_name:product(row.product_id)?.name,department_name:department(row.department_id)?.name}:row;
  return Object.values(joined).join(' ').toLocaleLowerCase('ru').includes(query);
 });
 $('record-count').textContent=`Записей: ${records.length}`;
 $('record-hint').textContent={products:'Цена в каталоге применяется к новым продажам. Сохранённые цены прошлых продаж остаются прежними.',departments:'Новый отдел создаётся вместе с первой продажей. Дневной план считается постоянным и используется для всех дат отчёта.',sales:'Последнюю продажу отдела нельзя удалить или перенести в другой отдел. Цена фиксируется при регистрации продажи.'}[entity];
 if(!records.length) { $('record-table').innerHTML=empty(query?'Ничего не найдено':'Записей пока нет',query?'Измените поисковый запрос.':'Нажмите кнопку добавления, чтобы создать первую запись.'); return; }
 const headers={products:['Артикул','Товар','Ед. измерения','Цена','Действия'],departments:['Код','Отдел','Заведующий','Телефон','Дневной план','Действия'],sales:['Дата','Товар','Отдел','Количество','Цена','Сумма','Действия']}[entity];
 const rows=records.map(row=>entity==='products'?[esc(row.sku),`<span class="cell-main">${esc(row.name)}</span>`,esc(row.unit),esc(money(row.retail_price)),actions(row)]:entity==='departments'?[esc(row.code),`<span class="cell-main">${esc(row.name)}</span>`,esc(row.manager_name),esc(row.phone),esc(money(row.daily_plan)),actions(row)]:[...saleRow(row),actions(row)]);
 $('record-table').innerHTML=table(headers,rows);
}
async function renderOverview() {
 const date=$('overview-date').value;
 if(!date) return;
 const {data}=await api('reports/daily?date='+encodeURIComponent(date));
 if(state.page!=='overview') return;
 const revenue=data.reduce((n,d)=>n+Number(d.revenue),0), plan=data.reduce((n,d)=>n+Number(d.daily_plan),0);
 $('metric-revenue').textContent=money(revenue);
 $('metric-plan').textContent=plan>0?number(revenue/plan*100)+' %':'—';
 $('metric-plan-note').textContent='Общий план: '+money(plan);
 $('metric-departments').textContent=state.departments.length;
 $('metric-products').textContent=state.products.length;
 $('department-bars').innerHTML=data.length?data.map(d=>`<div class="bar-item"><div class="bar-label"><span>${esc(d.name)}</span><b>${esc(money(d.revenue))}</b></div><div class="bar-track"><div class="bar-fill" style="width:${Math.min(100,Math.max(0,Number(d.completion_percent)||0))}%"></div></div><div class="bar-note">План ${esc(money(d.daily_plan))} · ${d.completion_percent===null?'процент не определён':esc(number(d.completion_percent))+' % выполнено'}</div></div>`).join(''):empty('Отделов пока нет','Сначала добавьте товар, затем создайте отдел с первой продажей.');
 const recent=state.sales.slice(-5).reverse();
 $('recent-sales').innerHTML=recent.length?table(['Дата','Товар','Отдел','Количество','Цена','Сумма'],recent.map(saleRow)):empty('Продажи ещё не зарегистрированы','Новые записи появятся здесь после добавления.');
}
const percent = v => v===null?'—':`<span class="progress-pill ${Number(v)<100?'low':''}">${esc(number(v))} %</span>`;
async function dailyReport() {
 if(!$('report-date').value) return;
 const {data}=await api('reports/daily?date='+encodeURIComponent($('report-date').value));
 $('daily-report').innerHTML=data.length?table(['Код','Отдел','План','Выручка','Отклонение','Выполнение'],data.map(d=>[esc(d.code),esc(d.name),esc(money(d.daily_plan)),esc(money(d.revenue)),esc(money(d.difference)),percent(d.completion_percent)])):empty('Нет отделов','Добавьте отдел, чтобы увидеть выполнение плана.');
}
async function demandReport() {
 const from=$('report-from').value,to=$('report-to').value;
 if(!from||!to) return;
 if(from>to) throw new Error('Начало периода должно быть не позже его окончания.');
 const {data}=await api(`reports/demand?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
 $('demand-report').innerHTML=data.length?table(['Артикул','Товар','Ед. измерения','Количество','Выручка','Число продаж'],data.map(p=>[esc(p.sku),esc(p.name),esc(p.unit),esc(number(p.quantity)),esc(money(p.revenue)),esc(number(p.sale_count))])):empty('Каталог пока пуст','Добавьте товары, чтобы анализировать спрос.');
}
async function renderPage() {
 const meta=pages[state.page];
 $('title').textContent=meta[0]; $('subtitle').textContent=meta[1]; $('breadcrumb').textContent=meta[2];
 document.querySelectorAll('nav a').forEach(a=>a.classList.toggle('active',a.dataset.page===state.page));
 $('overview').hidden=state.page!=='overview'; $('reports').hidden=state.page!=='reports';
 $('records').hidden=!['products','departments','sales'].includes(state.page);
 $('create-button').hidden=!meta[3]||!state.ready; $('create-button').textContent=meta[3]||'';
 if(!state.ready) return;
 if(state.page==='overview') await renderOverview();
 else if(state.page==='reports') await Promise.all([dailyReport(),demandReport()]);
 else renderRecords();
}
function navigate() { state.page=pages[location.hash.slice(1)]?location.hash.slice(1):'overview'; $('search').value=''; $('notice').hidden=true; renderPage().catch(e=>notify(friendlyError(e),true)); }
function fieldHTML([key,label,type,max], data, prefix='') {
 const name=prefix+key, value=data[key]??(type==='date'?today:'');
 let control;
 if(type==='product'||type==='department') {
  const list=type==='product'?state.products:state.departments;
  control=`<select name="${name}" aria-label="${esc(label)}" required><option value="">Выберите ${type==='product'?'товар':'отдел'}</option>${list.map(item=>`<option value="${item.id}" ${Number(value)===item.id?'selected':''}>${esc(item.name)} · ${esc(item.sku||item.code)}${type==='product'?' · '+esc(money(item.retail_price)):''}</option>`).join('')}</select>`;
 } else {
  const numeric=type==='money'||type==='quantity';
  control=`<input name="${name}" type="${numeric?'number':type}" value="${esc(type==='date'?String(value).slice(0,10):value)}" required ${numeric?`min="${type==='quantity'?'0.001':'0'}" step="${type==='quantity'?'0.001':'0.01'}" max="${type==='quantity'?'999999999.999':'999999999999.99'}"`:''} ${max?`maxlength="${max}"`:''}>`;
 }
 return `<label class="field ${key==='name'||key==='manager_name'?'wide':''}"><span>${label}</span>${control}</label>`;
}
function openEditor(id=null) {
 const entity=state.page;
 if(entity==='departments'&&!id&&!state.products.length) {notify('Сначала добавьте хотя бы один товар в каталоге.',true);return;}
 if(entity==='sales'&&(!state.products.length||!state.departments.length)) {notify('Сначала добавьте товар и отдел с первой продажей.',true);return;}
 state.editing=id;
 const data=id?state[entity].find(r=>r.id===id):{};
 $('editor-title').textContent=id?'Изменить запись':{products:'Новый товар',departments:'Новый отдел',sales:'Новая продажа'}[entity];
 $('form-fields').innerHTML=fields[entity].map(f=>fieldHTML(f,data)).join('');
 if(entity==='departments'&&!id) $('form-fields').insertAdjacentHTML('beforeend','<div class="form-subtitle">Первая продажа отдела</div><p class="form-note">У каждого отдела должна быть хотя бы одна продажа. Отдел и первая продажа будут сохранены вместе.</p>'+fields.sales.filter(f=>f[0]!=='department_id').map(f=>fieldHTML(f,{},'initial_sale.')).join(''));
 if(entity==='sales') $('form-fields').insertAdjacentHTML('beforeend','<p class="form-note">Цена берётся из каталога при создании продажи. При изменении количества или даты сохранённая цена остаётся прежней; при смене товара применяется его текущая цена.</p>');
 $('form-errors').hidden=true; $('editor').showModal();
}
async function saveRecord(event) {
 event.preventDefault(); const entity=state.page,id=state.editing;
 const values=Object.fromEntries(new FormData($('editor-form'))),payload={};
 for(const [key,value] of Object.entries(values)) {
  if(key.startsWith('initial_sale.')) {payload.initial_sale??={};payload.initial_sale[key.slice(13)]=value;}
  else payload[key]=value;
 }
 $('save-button').disabled=true; $('form-errors').hidden=true;
 try {
  await api(entity+(id?'/'+id:''),{method:id?'PATCH':'POST',body:JSON.stringify(payload)});
  $('editor').close(); await refreshData(); await renderPage(); notify(id?'Изменения сохранены.':'Запись добавлена.');
 } catch(e) { $('form-errors').textContent=friendlyError(e); $('form-errors').hidden=false; }
 finally { $('save-button').disabled=false; }
}
$('overview-date').value=today; $('report-date').value=today; $('report-to').value=today; $('report-from').value=today.slice(0,8)+'01';
window.addEventListener('hashchange',navigate);
$('search').addEventListener('input',renderRecords);
$('create-button').addEventListener('click',()=>openEditor());
$('close-editor').addEventListener('click',()=>$('editor').close());
$('cancel-editor').addEventListener('click',()=>$('editor').close());
$('editor-form').addEventListener('submit',saveRecord);
$('overview-date').addEventListener('change',()=>renderOverview().catch(e=>notify(friendlyError(e),true)));
$('report-date').addEventListener('change',()=>dailyReport().catch(e=>notify(friendlyError(e),true)));
$('demand-form').addEventListener('submit',e=>{e.preventDefault();demandReport().catch(e=>notify(friendlyError(e),true));});
$('record-table').addEventListener('click',e=>{
 const edit=e.target.closest('[data-edit]'),remove=e.target.closest('[data-delete]');
 if(edit) openEditor(Number(edit.dataset.edit));
 if(remove) {state.pendingDelete={entity:state.page,id:Number(remove.dataset.delete)};$('delete-dialog').showModal();}
});
$('delete-dialog').addEventListener('close',async()=>{
 if($('delete-dialog').returnValue!=='delete'||!state.pendingDelete) return;
 const {entity,id}=state.pendingDelete;state.pendingDelete=null;
 try {await api(entity+'/'+id,{method:'DELETE'});await refreshData();await renderPage();notify('Запись удалена.');}
 catch(e) {notify(friendlyError(e),true);}
});
navigate();
refreshData().then(renderPage).catch(e=>{$('connection').textContent='Нет соединения';notify(friendlyError(e),true);});
