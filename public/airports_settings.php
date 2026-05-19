<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
?>

<!-- <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<div class="card shadow-sm">

<div class="card-header d-flex justify-content-between align-items-center">
<h5 class="mb-0"><i class="fa fa-plane me-2"></i>Airports Manager</h5>

<button class="btn btn-primary btn-sm d-none" id="addAirport">
<i class="fa fa-plus me-1"></i>Add Airport
</button>

</div>


<div class="card-body">

<div class="row mb-3">

<div class="col-md-3">
<select id="filter_country" class="form-control select2">
<option value="">All Countries</option>
</select>
</div>

<div class="col-md-3">

<select id="filter_preferred" class="form-control">
<option value="">All Airports</option>
<option value="1">Preferred</option>
<option value="0">Normal</option>
</select>

</div>

</div>


<table id="airportsTable" class="table table-bordered table-hover align-middle">

<thead class="table-light">
<tr>

<th>ID</th>
<th>Code</th>
<th>Name</th>
<th>Country</th>
<th>Preferred</th>
<th>Actions</th>

</tr>
</thead>

</table>

</div>
</div>



<!-- Airport Modal -->

<div class="modal fade" id="airportModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Airport</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>


<div class="modal-body">

<form id="airportForm">

<input type="hidden" name="id" id="airport_id">

<div class="mb-3">
<label>Airport Code</label>
<input type="text" class="form-control" name="code" id="airport_code" maxlength="3">
</div>

<div class="mb-3">
<label>Airport Name</label>
<input type="text" class="form-control" name="name" id="airport_name">
</div>

<div class="mb-3">
<label>Country</label>
<select name="country" id="airport_country" class="form-control select2"></select>
</div>

</form>

</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-primary" id="saveAirport">Save</button>
</div>

</div>
</div>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->

<script>

/* -----------------------------
SELECT2
----------------------------- */

$('#airport_country').select2({
    width: '100%',
    theme: 'bootstrap-5',
    placeholder: 'Select Country',
    dropdownParent: $('#airportModal'),   // IMPORTANT
    ajax: {
        url: 'public/ajax/airports_settings.php',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                action: 'countries',
                search: params.term
            };
        },
        processResults: function (data) {
            return {
                results: data
            };
        },
        cache: true
    }
});

$(function(){



/* -----------------------------
DATATABLE
----------------------------- */

if ($.fn.DataTable.isDataTable('#airportsTable')) {
    $('#airportsTable').DataTable().destroy();
}

var table = $('#airportsTable').DataTable({

processing:true,
serverSide:false,

ajax:{
url:'public/ajax/airports_settings.php',
data:function(d){

d.action='list';
d.country=$('#filter_country').val();
d.preferred=$('#filter_preferred').val();

},
error:function(){
alert('Failed to load airports');
}
},

pageLength:25,
order:[[2,'asc']],

columns:[

{data:'id', width:'60px'},

{
data:'code',
render:function(d){
return '<span class="badge bg-secondary">'+d+'</span>';
}
},

{data:'name'},

{data:'country'},

{
data:'is_preferred',
orderable:false,
render:function(d,type,row){

return `
<div class="form-check form-switch">
<input class="form-check-input togglePreferred"
data-id="${row.id}"
type="checkbox"
${d==1?'checked':''}>
</div>
`;

}
},

{
data:null,
orderable:false,
className:'text-end',
render:function(d,type,row){

return `
<button class="btn btn-sm btn-outline-primary editAirport" data-id="${row.id}">
<i class="fa fa-edit"></i>
</button>

<button class="btn btn-sm btn-outline-danger deleteAirport d-none" data-id="${row.id}">
<i class="fa fa-trash"></i>
</button>
`;

}
}

]

});


/* -----------------------------
RELOAD HELPER
----------------------------- */

function reloadTable(){
table.ajax.reload(null,false);
}


/* -----------------------------
FILTERS
----------------------------- */

$('#filter_country,#filter_preferred').on('change',function(){
reloadTable();
});

function loadCountriesFilter(){

// $.getJSON(
// 'public/ajax/airports_settings.php?action=countries',
// function(data){

// $('#filter_country').append(
// '<option value="">All Countries</option>'
// );

// data.forEach(function(c){

// $('#filter_country').append(
// `<option value="${c.id}">${c.text}</option>`
// );

// });

// });

$('#filter_country').select2({
    width: '100%',
    theme: 'bootstrap-5',
    placeholder: 'All Countries',
    allowClear: true,
    ajax: {
        url: 'public/ajax/airports_settings.php',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                action: 'countries',
                type: 'filter',
                search: params.term || ''
            };
        },
        processResults: function (data) {

            // Add "All Countries" at top
            data.unshift({
                id: '',
                text: 'All Countries'
            });

            return {
                results: data
            };
        },
        cache: true
    }
});

}

loadCountriesFilter();


/* -----------------------------
ADD AIRPORT
----------------------------- */

$('#addAirport').click(function(){

$('#airportForm')[0].reset();

$('#airport_id').val('');

$('#airport_country').val(null).trigger('change');

$('#airportModal').modal('show');

});


/* -----------------------------
AUTO UPPERCASE CODE
----------------------------- */

$('#airport_code').on('keyup',function(){
this.value=this.value.toUpperCase();
});


/* -----------------------------
SAVE AIRPORT
----------------------------- */

$('#saveAirport').click(function(){

let btn=$(this);

btn.prop('disabled',true);

$.post(

'public/ajax/airports_settings.php?action=save',
$('#airportForm').serialize(),

function(res){

btn.prop('disabled',false);

if(res.success){

$('#airportModal').modal('hide');

reloadTable();

}else{

alert(res.msg || 'Save failed');

}

},'json').fail(function(){

btn.prop('disabled',false);
alert('Server error');

});

});


/* -----------------------------
EDIT AIRPORT
----------------------------- */

$('#airportsTable').on('click','.editAirport',function(){

let id=$(this).data('id');

$.getJSON(

'public/ajax/airports_settings.php?action=get',
{id:id},

function(res){

$('#airport_id').val(res.id);
$('#airport_code').val(res.code);
$('#airport_name').val(res.name);
// $('#airport_country').val(res.country).trigger('change');
var option = new Option(res.country, res.country, true, true);
$('#airport_country').append(option).trigger('change');

$('#airportModal').modal('show');

}

).fail(function(){

alert('Failed to load airport');

});

});


/* -----------------------------
DELETE AIRPORT
----------------------------- */

$('#airportsTable').on('click','.deleteAirport',function(){

if(!confirm('Delete this airport?')) return;

let id=$(this).data('id');

$.post(

'public/ajax/airports_settings.php?action=delete',
{id:id},

function(res){

reloadTable();

},'json').fail(function(){

alert('Delete failed');

});

});


/* -----------------------------
TOGGLE PREFERRED
----------------------------- */

$('#airportsTable').on('change','.togglePreferred',function(){

let id=$(this).data('id');
let flag=$(this).is(':checked')?1:0;

$.post(

'public/ajax/airports_settings.php?action=toggle_preferred',
{id:id,is_preferred:flag},

function(res){},

'json'

);

});

});

</script>