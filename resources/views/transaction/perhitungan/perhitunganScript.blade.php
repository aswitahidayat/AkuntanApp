<script type="text/javascript">
    var module = 'Order';
    var table = {};
    var fileData = [];
    var fileGambar = '';
    var selectId = 0;
    var detailId = 0;

    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        fill_datatable();

        $( "#subModalOrderAssumption" ).on('hidden.bs.modal', function(){
            $('#modalOrderAssumption').modal('show');
        });
        
        selectServiceDet()
        $('#ordhdr_service_dtl').change(function(){
            setService();
        })
    });

    function fill_datatable(name = ''){
        table = $(`#table${module}`).DataTable({
            initComplete: function(settings, json) {
                cekError(json.recordsFails)
            },
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            ajax: {
                    url: "{{ route('searchperhitungan') }}",
                    type: "POST",
                    data: {
                        name: name
                    }
                }, 
            columns: [
                {data: 'DT_RowIndex', orderable: false, searchable: false, name: 'DT_RowIndex'},
                {name: 'action', orderable: false, searchable: false, data: 'action'},
                {name: 'ordhdr_ordnum', data: 'ordhdr_ordnum'},//
                {name: 'ordprg_name', data: 'ordprg_name'},//
                {name: 'ordhdr_period_lastyear', data: 'ordhdr_period_lastyear'},//
                {name: 'paymentStatusName', data: 'paymentStatusName'},//
            ]

        });
    }

    $( `#formSearch${module}` ).submit(function() {
        $(`#table${module}`).DataTable().destroy();
        fill_datatable($("#search_name").val());
    });
    
    $(`#create${module}`).click(function () {
        $(`#saveBtn${module}`).html("Save");
        $(`#form${module}`).trigger("reset");
        //$(`#form${module}`)form.reset()
        $('#coytypehdr_id').val('');
        
        $('#tombol_upload').show()
        $('#fileupload').show()

        $('#modelHeading'+module).html(`Create New  ${module}`);
        $(`#modal${module}`).modal('show');
        $('#userUploadTbl').html('');
        $('#ordhdr_service_dtl').html('');
        $('#fileupload').val('');
        $('#fileupload').siblings(".custom-file-label").addClass("selected").html('');
        $(`#ordhdr_id`).val('');

        searchProgram(`#ordhdr_program`, '', `modal${module}`);
        searchPartner(`#ordhdr_bizpartid`, '', `modal${module}`);
    });

    $('body').on('click', `.edit${module}`, function () {
        var id = $(this).data('id');
        getDataAssumtion(id);
    });

    $('body').on('click', `.assumption${module}`, function () {
        var id = $(this).data('id');
        assumtionData(id)
    });

    $('body').on('click', '.assumptionView', function () {
        var id = $(this).data("id");
        assumtionData(id, true)
    });

    function assumtionData(id, disable = false){
        $.ajax({
            url: "{{ route('getassumption') }}",
            type: "POST",
            data: {
                ordhdr_id: id
            },
            success: function (datas) {
                let varHtml = ''
                
                getTemplate(datas.assumtionData, datas.periodCount, disable)
                $(`#modalOrderAssumption`).modal('show')
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }

    $('#fileupload').change(function(){
        var input = this;
        var url = $(this).val();

        if (this.files && this.files[0]) {
            var myFile = this.files[0];
            var reader = new FileReader();
            
            reader.addEventListener('load', function (e) {
                
                let csvdata = e.target.result; 
                data = csvFormatter(csvdata)
                fileData = data.data;
                setTemplateTableUser(fileData)
            });
            
            reader.readAsBinaryString(myFile);

            var fileName = url.split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        }

    });

    $('#com_fileupload').change(function(){
        var input = this;
        var url = $(this).val();

        if (this.files && this.files[0]) {
            var myFile = this.files[0];
            var reader = new FileReader();
            
            reader.addEventListener('load', function (e) {
                fileGambar = e.target.result;
            });
            
            var a = reader.readAsBinaryString(myFile);
            var fileName = url.split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        }

    });

    {{-- $('#btnUpload').click(function(){
        $('#userUploadTbl').html('');
        setTemplateTableUser(fileData)
    }) --}}

    $( `#form${module}` ).submit(function( e ) {
        $(`#saveBtn${module}`).html('Sending..');
        $(`#saveBtn${module}`).attr("disabled", true);
        
        var formdata = $(`#form${module}`).serializeArray();
        var dataall = {};
        $(formdata ).each(function(index, obj){
            dataall[obj.name] = obj.value;
        });
        
        dataall.detail = fileData;
        var sel = document.getElementById("ordhdr_service_dtl");
        // var price= sel.options[sel.selectedIndex].text;
        var price=  $('#service_hdr_view').html()
        price = formatCompCurrency(price)
        dataall.ordhdr_amount = price

        $.ajax({
            data: dataall,
            url: "{{ route('order.store') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $(`#form${module}`).trigger("reset");
                $(`#modal${module}`).modal('hide');
                table.draw();
                $(`#saveBtn${module}`).html('Save');
                $(`#saveBtn${module}`).removeAttr("disabled");
            },
            error: function (data) {
                console.log('Error:', data);
                $(`#saveBtn${module}`).html('Save');
                $(`#saveBtn${module}`).removeAttr("disabled");

            }
        });
    });

    function getDataAssumtion(id, disable = false){
        $.get("{{ route('order.index') }}" +`/${id}/edit`, (data) => {
            $(`#form${module}`).trigger("reset");
            $(`#subdis_provid`).html("");
            $('#modelHeading'+module).html(`Edit ${module}`);
            $(`#saveBtn${module}`).html("Edit") 
                
            $('#fileupload').val('');

            $('#tombol_upload').hide()
            $('#fileupload').hide()

            if(data.ordhdr_date){
                data.ordhdr_date = dateFormat(data.ordhdr_date)
            }

            if(data.ordhdr_pay_date){
                data.ordhdr_pay_date = dateFormat(data.ordhdr_pay_date)
            }
            $.each(data, (key,val) => {
                $(`#${key}`).val(val);
                //$(`#${key}`).prop("disabled", disable)
            });

            $.ajax({
                url: "{{ route('getorderdetail') }}",
                type: "POST",
                data: {
                    ordhdr_id: id
                },
                success: function (datas) {
                    setTemplateTableUser(datas);
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });

            searchProgram(`#ordhdr_program`, data.ordhdr_program, `modal${module}`);
            searchPartner(`#ordhdr_bizpartid`,  data.ordhdr_bizpartid, `modal${module}`);
            selectServiceDet(data.ordhdr_service_hdr, data.ordhdr_service_dtl)

            $(`#modal${module}`).modal('show');

        })
    }

    function getDataOrderDisable(id){
        $.get("{{ route('order.index') }}" +`/${id}/edit`, (data) => {
            $(`#form${module}`).trigger("reset");
            $(`#subdis_provid`).html("");
            $('#modelHeading'+module).html(`Edit ${module}`);
                
            $('#fileupload').val('');

            $('#tombol_upload').hide()
            $('#fileupload').hide()

            if(data.ordhdr_date){
                data.ordhdr_date = dateFormat(data.ordhdr_date)
            }

            if(data.ordhdr_pay_date){
                data.ordhdr_pay_date = dateFormat(data.ordhdr_pay_date)
            }
            $.each(data, (key,val) => {
                $(`#view_${key}`).html(val);
            });

            $.ajax({
                url: "{{ route('getorderdetail') }}",
                type: "POST",
                data: {
                    ordhdr_id: id
                },
                success: function (datas) {
                    setTemplateTableUser(datas)
                },
                error: function (data) {
                    console.log('Error:', data)
                }
            });

            selectServiceDet(data.ordhdr_service_hdr, data.ordhdr_service_dtl)
            $(`#viewOrder`).modal('show');

        })
    }
    function setTemplateTableUser(varData){
        var tblHtml = '';

        try {
            varData.forEach(function(item, index){
                var q = new Date()
                var m = q.getMonth()
                var d = q.getDay()
                var y = q.getFullYear()
    
                var date = new Date(y,m,d)
                
                if(isNaN(new Date(item.Birthdate).getTime())){
                    throw new Error(`Birthdate No:${index + 1} - NPK:${item.NPK} Salah`)
                }

                if(isNaN(new Date(item.Startdate).getTime())){
                    throw new Error(`Startdate No:${index + 1} - NPK:${item.NPK} Salah`)
                }

                if(new Date(item.Birthdate) > date){
                    throw new Error(`Birthdate No:${index + 1} - NPK:${item.NPK} Salah`)
                }

                if(isNaN(parseFloat(item.Salery)) ){
                    throw new Error(`Salery No ${index + 1} - ${item.NPK} Salah`)
                }
                tblHtml += '<tr>';
                tblHtml += `<td> ${index + 1} </td>`;
                tblHtml += `<td> ${item.NPK ? item.NPK : '-'} </td>`;
                tblHtml += `<td> ${item.Name ? item.Name : '-'} </td>`;
                tblHtml += `<td> ${item.Gender ? item.Gender.toUpperCase() : '-'} </td>`;
                tblHtml += `<td> ${item.Birthdate ? formatHumanDate(item.Birthdate) : '-'} </td>`;
                tblHtml += `<td> ${item.KTP ? item.KTP : '-'} </td>`;
                tblHtml += `<td> ${item.NPWP ? item.NPWP : '-'} </td>`;
                tblHtml += `<td> ${item.Address ? item.Address : '-'} </td>`;
                tblHtml += `<td> ${item.HP ? item.HP : '-'} </td>`;
    
                tblHtml += `<td> ${item.Startdate ? formatHumanDate(item.Startdate) : '-'} </td>`;
                tblHtml += `<td> ${item.Salery ? formatHumanCurrency(item.Salery) : '-'} </td>`;
    
                tblHtml += '</tr>';
            });

            var varHtml = ` <table id="tableUserUpload" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th width="100px">NPK</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Birthdate</th>
                        <th>KTP</th>
                        <th>NPWP</th>
                        <th>Address</th>
                        <th>HP</th>
                        <th>Startdate</th>
                        <th>Salery</th>
    
                    </tr>
                </thead>
    
                <tbody>
                    ${tblHtml}
                </tbody>
            </table>`
    
            $('#userUploadTbl').html(varHtml)
        } catch(e){
            var a =`<div class="alert alert-danger" role="alert">
                <strong>${e}</strong>
              </div>`
            $('#userUploadTbl').html(a)
            $('#fileupload').siblings(".custom-file-label").addClass("selected").html('')
        }
    }

    function selectServiceDet(id, selected){
        if(selected){

            $.ajax({
                url: "{{ route('servicedetail') }}",
                type: "POST",
                dataType: 'json',
                data:{
                },
                success: function (datas) {
                    let varHtml = '';
                    $.each(datas, (key, item) => {
                        let price = formatHumanCurrency(item.ordsrvdtl_price);
                        varHtml +=  `<option value="${item.ordsrvdtl_id}" >${item.ordsrvdtl_desc}</option>`
                    });
                    $('#ordhdr_service_dtl').html(varHtml);
                    $('#ordhdr_service_dtl').val(selected);
                    setService();
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        } 

        $('#ordhdr_service_dtl').select2({
            placeholder: 'Cari...',
            dropdownParent: $(`#modal${module}`),
            ajax: {
                url: "{{ route('servicedetail') }}",
                dataType: 'json',
                delay: 250,
                type: 'POST',
                data: function (params, page){
                    return{
                        name: params.term
                    }
                },
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.ordsrvdtl_desc,
                                id: item.ordsrvdtl_id
                            }
                        })
                    };
                },
                cache: true
            }
        });

    }

    function setService(){
        $.ajax({
            url: "{{ route('getdetail') }}",
            type: "POST",
            dataType: 'json',
            data:{
                ordsrvdtl_id: $('#ordhdr_service_dtl').val()
            },
            success: function (datas) {
                var curency = formatHumanCurrency(datas.ordsrvdtl_price);
                $('#ordhdr_service_hdr').val(datas.ordsrvdtl_hdrid);
                $('#service_hdr_view').html(curency);

            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }

    function getTemplate(vardata ={}, periodeCount, disable){
        $(`#assumption`).html('');
        var setdisable = disable ? 'disabled' : ''
        let provHtml = '';
                
        var countData = 0;

        var periode = '0';
        $.each(vardata, (key, ass) => {
            var opsi = '';
            var title = '';
            var input = ''
            if(ass.ordass_sp == 'P'){
                opsi =`<option value="S" >Single</option>
                            <option value="P" selected>Progresive</option>`;
                input = `<input type="button" name="ordass_value" class="btn btn-secondary btn-sm" value="Edit Progresive" onclick="showSubModal('${ass.ordass_id}', ${disable})" />`
            } else {
                opsi =`<option value="S" selected>Single</option>
                            <option value="P" >Progresive</option>`;
                input = `<input type="number" name="ordass_value" class="form-control" id="form_assumption_${ass.assump_templ_code}_val"
                                class="col-xs-10 col-xs-5" value=${ass.ordass_value} required ${setdisable}/>`
            }

            if (periode != ass.ordass_periode){
                title = `<div class="form-group"><h2>Detail ${ass.ordass_periode}</h2></div>`;
                periode = ass.ordass_periode
            }
            provHtml +=  `${title}`;
            if(ass.mortarita_flag == 'Y'){
                var idmor = `form_assumption_${ass.assump_templ_code}_val_${ass.ordass_id}`;
                getMortarita(idmor);
                provHtml += `
                    <form class="form-assumption">
                        <div class="form-group" style="display: flex;">
                            <label class="control-label col-sm-2" for="form-field-1"> ${ass.assump_templ_desc}</label>
                            <div class="col-sm-10">
                                <input type="hidden" name="ordass_id" id="ordass_id" value="${ass.ordass_id}" />
                                <input type="hidden" id="form_assumption_${ass.ordass_id}_sp" 
                                    name="coytypedtl_assumpt_sp" value="S" ${setdisable}/>
                                <select type="number" name="ordass_value" class="form-control" id="${idmor}"
                                    class="col-xs-10 col-xs-5"  value=${ass.ordass_value} ${setdisable}>
                                </select>
                            </div>
                        </div>
                    </form>
                `
            } else {
                provHtml +=  `
                    <div class="form-group" style="display: flex;">
                        <label class="control-label col-sm-2" for="form-field-1"> ${ass.assump_templ_desc}</label>
                        <div class="col-sm-10">
                            <form class="form-assumption">
                                <div class="col-xs-3">
                                    <input type="hidden" name="ordass_id" id="ordass_id" value="${ass.ordass_id}" ${setdisable}/>
                                    <select class="form-control select2-single" style="width:100%;" 
                                        onchange="changeAssumption('${ass.ordass_id}', this.value)" 
                                        id="form_assumption_${ass.ordass_id}_sp"
                                        name="coytypedtl_assumpt_sp" ${setdisable}>
                                        ${opsi}
                                    </select>
                                </div>
                                <div class="col-xs-9" id="form_assumption_${ass.ordass_id}_div">
                                    ${input}
                                </div>
                            </form>
                        </div>

                    </div>`;
            }
            countData++;
            
        });
        
        $("#submitModal").prop("disabled", disable);
        
        $(`#assumption`).html(provHtml);
    }

    function showSubModal(id = 0, disable){
        $('#modalOrderAssumption').modal('hide');
        $(`#subModalOrderAssumption`).modal('show');
        getProgressive(id, disable)
    }

    function getProgressive(id, disable){
        $(`#subModalid`).val(id);
        $.ajax({
            url: "{{ route('getprogressive') }}",
            type: "POST",
            data: {
                ordpro_assid: id
            },
            success: function (datas) {
                let varHtml = '';
                setdisable = disable ? 'disabled' : ''

                $.each(datas, (key, ass) => {
                    var varid = ass.ordpro_id ? ass.ordpro_id : '';
                    var varvalue = ass.ordpro_value ? ass.ordpro_value : 0;
                    var varmin = ass.ordpro_amt_min ? ass.ordpro_amt_min : 0;
                    var varmax = ass.ordpro_amt_max ? ass.ordpro_amt_max : 0;
                    varHtml +=  `
                    <form class="progresive_form" style="display: flex;" >
                        <input type="hidden" name="ordpro_id" value ="${varid}" />
                        <input type="hidden" name="ordpro_assid" value ="${id}" />
                        <div class="col-xs-4">
                            <label class="control-label no-padding-right" for="form-field-1">Min</label>
                            <input type="number" min="0" class="form-control" name="ordpro_amt_min"
                            class="col-xs-10 col-xs-5" required value="${varmin}" ${setdisable}/>
                        </div>
                        <div class="col-xs-4">
                            <label class="control-label no-padding-right" for="form-field-1">Max</label>
                            <input type="number" min="0" class="form-control" name="ordpro_amt_max"
                            class="col-xs-10 col-xs-5" required value="${varmax}" ${setdisable}/>
                        </div>
                        <div class="col-xs-4">
                            <label class="control-label no-padding-right" for="form-field-1">Value</label>
                            <input type="number" min="0" class="form-control" name="ordpro_value"
                            class="col-xs-10 col-xs-5" required value="${varvalue}" ${setdisable}/>
                        </div>
                    </form>`;

                })

                $(`#subAssumption`).html(varHtml);
                $( "#submitSubModal" ).prop( "disabled", disable );
                $( "#addProgressive" ).prop( "disabled", disable );

            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }

    function addProgressive(){
        let varHtml = '';
        var id =  $(`#subModalid`).val();
        varHtml +=  `
            <form class="progresive_form" style="display: flex;" >
                <input type="hidden" name="ordpro_id" value ="" />
                <input type="hidden" name="ordpro_assid" value ="${id}" />
                <div class="col-xs-4">
                    <label class="control-label no-padding-right" for="form-field-1">Min</label>
                    <input type="number" min="0" class="form-control" name="ordpro_amt_min"
                    class="col-xs-10 col-xs-5" required value=""/>
                </div>
                <div class="col-xs-4">
                    <label class="control-label no-padding-right" for="form-field-1">Max</label>
                    <input type="number" min="0" class="form-control" name="ordpro_amt_max"
                    class="col-xs-10 col-xs-5" required value=""/>
                </div>
                <div class="col-xs-4">
                    <label class="control-label no-padding-right" for="form-field-1">Value</label>
                    <input type="number" min="0" class="form-control" name="ordpro_value"
                    class="col-xs-10 col-xs-5" required value="" />
                </div>
            </form>`;

        $("#subAssumption").append(varHtml);
    }

    function changeAssumption(key, typ){
        varHtml= '';
        $(`#assumption_${key}_div`).html('');
        
        if(typ == 'S'){
            varHtml = `
                <label class="control-label no-padding-right" for="form-field-1">&nbsp;</label> 
                <input type="number" min="0" name="ordass_value" class="form-control" id="form_assumption_${key}_val"
                    class="col-xs-10 col-xs-5" required/>`;
        } else if (typ == 'P') {
            varHtml = `
            <input type="button" class="btn btn-secondary btn-sm" value="Edit Progresive" onclick="showSubModal('${key}')"/>

            `;
        }

        $(`#form_assumption_${key}_div`).html(varHtml);
    }

    function comfirmOrder(id){
        $.get("{{ route('order.index') }}" +`/${id}/edit`, (data) => {
            if(data.ordhdr_date){
                data.ordhdr_date = dateFormat(data.ordhdr_date)
            }

            if(data.ordhdr_pay_date){
                data.ordhdr_pay_date = dateFormat(data.ordhdr_pay_date)
            }

            $.each(data, (key,val) => {
                $(`#com_${key}`).val(val);
            });
            $(`#comfirmOrder`).modal('show');
        })
        
    }
    function hitungOrder(orderid, ordernum){
        if(confirm("Yakin mau hitung ?")){
            $.ajax({
                type: "POST",
                data:{
                    orderid: orderid,
                    ordernum:ordernum,
                },
                url: "{{ route('hitungorder') }}",
                success: function (data) {
                    alert("Proses perhitungan sudah dilakukan");
                    table.draw();
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        }
    }

    function getMortarita(id){
        $.ajax({
            url: "{{ route('searchmortalita') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                let letHtml = '';
                $.each(data.data, (key, val) => {
                    letHtml +=  `<option value="${val.mortalitahdr_id}" >${val.mortalitahdr_name}</option>`
                });
                $(`#${id}`).html(letHtml);
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }
    
    $("#submitSubModal").click(function(){
        var dataReturn =[]
        $(".progresive_form").each(function(index, data){
            var dataObj = {}
            dataObj[data[0].name] = data[0].value
            dataObj[data[1].name] = data[1].value
            dataObj[data[2].name] = data[2].value
            dataObj[data[3].name] = data[3].value
            dataObj[data[4].name] = data[4].value
            dataReturn.push(dataObj);
        })
        console.log(dataReturn);

        $.ajax({
            url: "{{ route('setprogressive') }}",
            type: "POST",
            data: {
                dataProgressive: dataReturn
            },
            success: function (datas) {
                alert("Progresive berhasil disimpan");
                $('#modalOrderAssumption').modal('show');
                $(`#subModalOrderAssumption`).modal('hide');
                table.draw();
            },
            error: function (data) {
                console.log(data)
            }
        })
    })

    $( `#submitModal` ).click(function( e ) {
        var dataReturn =[]
        $(".form-assumption").each(function(index, data){
            if(data[1].value == "S"){
                var dataObj = {}
                dataObj[data[0].name] = data[0].value
                dataObj[data[1].name] = data[1].value
                dataObj[data[2].name] = data[2].value
                dataReturn.push(dataObj);
            }
        })   

        $.ajax({
            url: "{{ route('setassumption') }}",
            type: "POST",
            data: {
                dataAssumption: dataReturn
            },
            success: function (datas) {
                alert("Assumption berhasil disimpan");
                $('#modalOrderAssumption').modal('hide');
                table.draw();
            },
            error: function (data) {
                console.log(data)
            }
        })
    })

    function submitComfirmOrder(e){
        var formData = new FormData(); // Currently empty
        formData.append('id', $('#com_ordhdr_id').val());
        formData.append('num', $('#com_ordhdr_ordnum').val());
        formData.append('file', $('#com_fileupload').prop('files')[0] );

        $.ajax({
            data: formData,
            url: "{{ route('comfirmorder') }}",
            type: "POST",
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (data) {
                $(`#comfirmOrder`).modal('hide');
                table.draw();
            },
            error: function (data) {
                $(`#modal${module}`).modal('hide');
                console.log('Error:', data);
            }
        });
    }

    function viewOrder(id){
        $('#orderHasil').modal('show');
        selectId = id;
        getKaryawan(id);
        getTahun(id);
        setHasilTabel();
        setHasilTabelDtl()
    }

    function getKaryawan(id){
        $.ajax({
            url: "{{ route('getkaryawan') }}",
            type: "POST",
            data: {
                ordhdr_id: id
            },
            success: function (datas) {
                let letHtml = '';
                letHtml +=  `<option value="" >Semua</option>`
                $.each(datas, (key, val) => {
                    letHtml +=  `<option value="${val.orddtl_id}" >${val.orddtl_name}</option>`
                });

                $(`#search_hasil_karyawan`).html(letHtml);
            },
            error: function (data) {
                console.log(data)
            }
        })
    }

    function getTahun(id){
        
        $.ajax({
            url: "{{ route('gettahun') }}",
            type: "POST",
            data: {
                ordhdr_id: id
            },
            success: function (datas) {
                let letHtml = '';
                letHtml +=  `<option value="" >Semua</option>`
                $.each(datas, (key, val) => {
                    letHtml +=  `<option value="${val.ordass_periode}" >${val.ordass_periode}</option>`
                });

                $(`#search_hasil_tahun`).html(letHtml);
            },
            error: function (data) {
                console.log(data)
            }
        })
    }

    function formCariHasil(){
        var formData = new FormData(); // Currently empty
        formData.append('ordchdr_orddtlid', $('#search_hasil_karyawan').val());
        formData.append('ordchdr_periode', $('#search_hasil_tahun').val());
        formData.append('orddtl_hdrid', selectId);

        $.ajax({
            data: formData,
            url: "{{ route('carihasil') }}",
            type: "POST",
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (data) {
                $(`#orderHasilHdr`).html()
                setHasilTabel(data)
                table.draw()
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }

    function hasilDtl(id){
        detailId = id
        $.ajax({
            data: {
                ordcdtl_ordchdrid: detailId,
            },
            url: "{{ route('carihasildtl') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $(`#orderHasilHdr`).html()
                setHasilTabelDtl(data)
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    }

    function setHasilTabel(datas){
        if(datas){

            let letHtml = ''; 
            $.each(datas, (key, val) => {
                letHtml +=`<tr class="grabbing" onclick="hasilDtl(${val.ordchdr_id})">`
                letHtml +=  `<td>${key + 1}</td>`
                letHtml +=  `<td nowrap>${val.orddtl_npk}</td>`
                letHtml +=  `<td nowrap>${val.orddtl_name}</td>`
                letHtml +=  `<td>${val.orddtl_sex}</td>`
                letHtml +=  `<td nowrap>${formatHumanDate(val.orddtl_startdate)}</td>`
                letHtml +=  `<td nowrap>${formatHumanDate(val.ordchdr_pensiondate)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_val_sal)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_pension_sal)}</td>`
                letHtml +=  `<td>${val.ordchdr_age_val}</td>`                
                letHtml +=  `<td>${val.ordchdr_past_srv}</td>`
                letHtml +=  `<td>${val.ordchdr_future_srv}</td>`
                letHtml +=  `<td>${val.ordchdr_total_srv}</td>`
                letHtml +=  `<td>${val.ordchdr_factor_m}</td>`
                letHtml +=  `<td>${val.ordchdr_factor_d}</td>`
                letHtml +=  `<td>${val.ordchdr_factor_w}</td>`
                letHtml +=  `<td>${val.ordchdr_factor_r}</td>`
                letHtml +=  `<td>${val.ordchdr_factor_increase}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_amt_ben_m)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_amt_ben_d)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_amt_ben_w)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_amt_ben_r)}</td>`
                letHtml +=  `<td>${val.ordchdr_prob_m}</td>`
                letHtml +=  `<td>${val.ordchdr_prob_d}</td>`
                letHtml +=  `<td>${val.ordchdr_prob_w}</td>`
                letHtml +=  `<td>${val.ordchdr_prob_r}</td>`
                letHtml +=  `<td>${val.ordchdr_diskonto}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_svc_sum)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordchdr_pbo_sum)}</td>`
                letHtml +=  `<td>${(val.ordchdr_age_val - val.ordchdr_age_work).toFixed(2)}</td>`
                letHtml +=`</tr>`
            });
    
            $('#orderHasilHdr').html(letHtml)
        } else (
            $('#orderHasilHdr').html(`<tr>
                    <td colspan="100%">-</td>
                </tr>`)
        )
    }

    function setHasilTabelDtl(datas){
        if(datas){

            let letHtml = ''; 
            $.each(datas, (key, val) => {
                letHtml +=`<tr>`
                letHtml +=  `<td>${key + 1}</td>`
                letHtml +=  `<td>${val.ordcdtl_age}</td>`
                letHtml +=  `<td>${val.ordcdtl_past_serv}</td>`
                letHtml +=  `<td>${val.ordcdtl_future_serv}</td>`
                letHtml +=  `<td>${parseFloat(parseFloat(val.ordcdtl_past_serv) + parseFloat(val.ordcdtl_future_serv)).toFixed(2)}</td>`
                letHtml +=  `<td>${val.ordcdtl_factor_ben_m}</td>`
                letHtml +=  `<td>${val.ordcdtl_factor_ben_d}</td>`
                letHtml +=  `<td>${val.ordcdtl_factor_ben_w}</td>`
                letHtml +=  `<td>${val.ordcdtl_factor_ben_r}</td>`
                letHtml +=  `<td>${val.ordcdtl_factor_increase}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_salary)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_amt_ben_m)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_amt_ben_d)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_amt_ben_w)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_amt_ben_r)}</td>`
                letHtml +=  `<td>${val.ordcdtl_prob_m}</td>`
                letHtml +=  `<td>${val.ordcdtl_prob_d}</td>`
                letHtml +=  `<td>${val.ordcdtl_prob_w}</td>`
                letHtml +=  `<td>${val.ordcdtl_prob_r}</td>`
                letHtml +=  `<td>${val.ordcdtl_diskonto}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pvdbo_m)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pvdbo_d)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pvdbo_w)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pvdbo_r)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pvdbo_sum)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_svc_m)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_svc_d)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_svc_w)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_svc_r)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_svc_sum)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pbo_m)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pbo_d)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pbo_w)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pbo_r)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_pbo_sum)}</td>`
                letHtml +=  `<td>${formatHumanCurrency(val.ordcdtl_salary)}</td>`
                
                letHtml +=`</tr>`
            });
    
            $('#orderHasilDtl').html(letHtml)
        } else (
            $('#orderHasilDtl').html(`<tr><td colspan="100%">-</td></tr>`)
        )
    }
    
    function cekError(datas){
        $(`#modalOrderFailBody`).html('');
        if(datas.length > 0){
            var txt= `Order dengan nomor berikut gagal dihitung: 
            <ul>`
                $.each(datas, (key, val) => {
                    txt += `<li>${val.fail_ordhdr_ordnum} - ${val.msg}</li>`
                })
                
                txt += `</ul>`

                $(`#modalOrderFailBody`).html(txt);
            $(`#modalOrderFail`).modal('show');
        }
    }
</script>