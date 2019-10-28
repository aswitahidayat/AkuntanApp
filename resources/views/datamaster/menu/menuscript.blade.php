<script type="text/javascript">
    $(function () {
        var module ='Menu';
        var table = {};
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        //show data
        function fill_datatable(name = '') {
            table = $(`#table${module}`).DataTable({
                ajax: {
                    url: "{{ route('searchmenuview') }}",
                    type: "POST",
                    data: {
                        name: name
                    }
                }, 
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                    {name: 'action', orderable: false, searchable: false,data: 'action'},
                    {name: 'mn_name', data: 'mn_name'},//
                    {name: 'mn_status_name', data: 'mn_status_name'},//
                ]
            });
        }

        fill_datatable();
 
        $( `#formSearch${module}` ).submit(function() {
            $(`#table${module}`).DataTable().destroy();
            fill_datatable($("#search_name").val());
        });


        //show modal
        $(`#create${module}`).click(function () {
            $('#saveBtn').html('Save');
            $('#usertype_id').val('');
            $('#MenuForm').trigger("reset");
            $('#modelHeading').html("Create New Menu");
            $('#saveBtn').removeAttr("disabled");
            $('#ajaxModel').modal('show');
        });

        //ad & ed
        $('body').on('click', `.edit${module}`, function () {
            var id = $(this).data('id');
            $.get("{{ route('menu.index') }}" +'/' + id +'/edit', function (data) {
                $('#modelHeading').html("Edit Menu");
                $('#saveBtn').html("Edit");
                $('#ajaxModel').modal('show');

                $.each(data, (key,val) => {
                    if($(`#${key}`).length) $(`#${key}`).val(val);
                });
                if(data.mn_status == 1){
                    $("#mn_status_active").prop("checked", true);
                } else if(data.mn_status != 1){
                    $("#mn_status_inactive").prop("checked", true);
                }

                var varHtml = '';

                $.each(data.permit, (key,val) => {
                    varHtml += `<input type="checkbox" name="mn_${val.usertype_id}" value="1" checked>${val.usertype_name}<br>`
                
                });

                $('#menuPremitDiv').html(varHtml)
                

            })
        });

        $( "#MenuForm" ).submit(function( e ) {
            e.preventDefault();

            var a = $('#MenuForm').serialize()
            debugger;
            /*

            $('#saveBtn').html('Sending..');
            $("#saveBtn").attr("disabled", true);
            $.ajax({
                data: $('#MenuForm').serialize(),
                url: "{{ route('usertype.store') }}",
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $('#MenuForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    table.draw();
                    $('#saveBtn').html('Save');
                    $('#saveBtn').removeAttr("disabled");
                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#saveBtn').html('Save');
                    $('#saveBtn').removeAttr("disabled");
                }
            });
            */
        });

        //delete
        $('body').on('click', '.deleteUsertype', function () {
            var id = $(this).data("id");
            if(confirm("Are You sure want to delete !")){
                $.ajax({
                    type: "DELETE",
                    url: "{{ route('usertype.store') }}"+'/'+id,
                    success: function (data) {
                        table.draw();
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });
            }
        });
    });
</script>