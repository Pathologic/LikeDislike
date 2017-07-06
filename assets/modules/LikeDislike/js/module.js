var columns = [ [
    {
        field:'pagetitle',
        title:'Название документа',
        sortable:true,
        width:200,
        formatter: function(value,row) {
            return row.crumbs + '<b>'+value
                .replace(/&/g, '&amp;')
                .replace(/>/g, '&gt;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;')+'</b>';
        }
    },
    {
        field:'updatedon',
        width:100,
        fixed:true,
        align:'center',
        title:'Обновлено',
        sortable:true,
        formatter:function(value) {
            sql = value.split(/[- :]/);
            d = new Date(sql[0], sql[1]-1, sql[2], sql[3], sql[4], sql[5]);
            year = d.getFullYear();
            month = d.getMonth()+1;
            day = d.getDate();
            hour = d.getHours();
            min = d.getMinutes();
            return ('0'+day).slice(-2) + '.' + ('0'+month).slice(-2) + '.' + year + '<br>' + ('0'+hour).slice(-2) + ':' + ('0'+min).slice(-2);
        }
    },
    {
        field:'like',
        width:80,
        fixed:true,
        align:'center',
        title:'<span style="color:green;" class="fa fa-lg fa-thumbs-o-up"></span>',
        sortable:true
    },
    {
        field:'dislike',
        width:80,
        fixed:true,
        align:'center',
        title:'<span style="color:red;" class="fa fa-lg fa-thumbs-o-down"></span>',
        sortable:true
    },
    {
        field:'ld_rating',
        width:80,
        fixed:true,
        align:'center',
        title:'<span style="color:#9c27b0;" class="fa fa-lg fa-heartbeat"></span>',
        formatter: function(value) {
            if (value < 0) {
                return '<span style="color:red;">' + value + '</span>';
            } else {
                return value;
            }
        },
        sortable:true
    },
    {
        field:'action',
        width:40,
        title:'',
        align:'center',
        fixed:true,
        formatter:function(value,row){
                return '<a class="action delete" href="javascript:void(0)" onclick="GridHelper.reset('+row.rid+')" title="Обнулить"><i class="fa fa-eraser fa-lg"></i></a>';
        }
    }
] ];
var GridHelper = {
    reset: function(rid) {
        if (rid){
            $.post(
                Config.url+'?mode=reset',
                {
                    rid:rid
                },
                function(data) {
                    if(data.success) {
                        $('#likedislike').datagrid('reload');
                    } else {
                        $.messager.alert('Ошибка','Не удалось выполнить');
                    }
                },'json'
            ).fail(GridHelper.handleAjaxError);
        }
    },
    handleAjaxError: function(xhr){
        var message = xhr.status == 200 ? 'Не удалось обработать ответ сервера' : 'Ошибка сервера ' + xhr.status + ' ' + xhr.statusText;
        $.messager.alert('Ошибка', message, 'error');
    },
    initGrid: function () {
        $('#likedislike').datagrid({
            url: Config.url,
            fitColumns:true,
            pagination:true,
            pageSize:50,
            pageList: [ 50, 100, 150, 200 ],
            idField:'rid',
            singleSelect:true,
            striped:true,
            checkOnSelect:false,
            selectOnCheck:false,
            columns: columns
        });
    }
};

