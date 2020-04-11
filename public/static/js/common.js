//获取参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

//内容加载
function contentLoad(module,obj) {
    // let module = 'order'//getUrlParam('module');
    var This = obj;
    if(This){
        This.addClass('layui-this');
        This.siblings('li').removeClass('layui-this');
    }
    if(module){
        let base_path = './view/';
        if(module === 'index') return;
        $('.layui-body').load(base_path+module+'.html');
    }
}