var columns = [ [
    {
        field:'pagetitle',
        title:'Название документа',
        sortable:true,
        width:200,
        formatter: function(value) {
            return value
                .replace(/&/g, '&amp;')
                .replace(/>/g, '&gt;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;');
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

