

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

});

$(document).ready(function(){
    $(".data input").keyup(function(){
        $(this).val(formataData($(this).val()))
    })
    
    $(".money input").maskMoney({ 
        allowNegative: false,
        thousands:'.',
        decimal:',',
        affixesStay: true
    })
    
    $('input[type=name]').bind('input',function(){
        str = $(this).val().replace(/[^A-Za-z\u00C0-\u00FF\-\/\s]+/g,'');
        str = str.replace(/[\s{ \2 }]+/g,' ');
        if(str == " ")str = "";
        $(this).val( str );
    });
    
    $('textarea').bind('textarea',function(){
        str = $(this).val().replace(/[^A-Za-z\u00C0-\u00FF\-\/\s]+/g,'');
        str = str.replace(/[\s{ \2 }]+/g,' ');
        if(str == " ")str = "";
        $(this).val( str );
    });

    $('input[type=name]').bind('input',function(){
        str = $(this).val().replace(/[^A-Za-z\u00C0-\u00FF\-\/\s]+/g,'');
        str = str.replace(/[\s{ \2 }]+/g,' ');
        if(str == " ")str = "";
        $(this).val( str );
    });
    
    $("input[name=cnpj]").keyup(function(){
        $(this).val(formataCnpj($(this).val()))
    })
    
    $(".error-input").hide()
})
//TRATA OS VALORES MONETÁRIOS
function trataValor(valor,tratamento){
    if(tratamento == 0){
        //TRATAENTO PARA EXIBIR NA TELA
        return Intl.NumberFormat('pt-br', {style: 'currency', currency: 'BRL'}).format(valor).replace("R$","").trim()
    }else{
        //TRATAMENTO PARA PROCESSAR NO BACKEND
        var quantidade = 0;
        for (var i = 0; i < valor.length; i++) {
            if (valor[i] == "," || valor[i] == "." ) {
                quantidade++
            }
        }
        //PERGUNTA SE A QUANTIDADE DE VIRGULAS E IGUAL A DOIS
        if(quantidade == 2){
            val = valor.replace(",",".").replace(".","")
        }else{
            val = valor.replace(",",".").trim()
        }
        return val.replace(",",".")
    }
}

function formataData(num){
    var str = "";
    num = num.replace(/[^0-9]+/g,'');
    num = num.substring(0,8);
    for(i=0;i < num.length; i++){
        if(i==2){str = str +'/'};
        if(i==4){str = str +'/'};
        str = str+ (num[i].toString());
    }
    return str;
}

//TRATA OS FORMULARIOS
function validaCampos(form){
    var inputs = [];
    $("input").parent().find(".error-input").hide()
    $("label").removeClass("text-danger")
    $("input").removeClass("border-danger")

    $("select").parents(".select").find(".error-input").hide()
    $("label").removeClass("text-danger")
    $("select").removeClass("border-danger")

    $("textarea").parent().find(".error-input").hide()
    $("label").removeClass("text-danger")
    $("textarea").removeClass("border-danger")

    $("input:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val().length < $(this).attr("minlength")){
                inputs.push($(this).attr("name"))
            }
        }
    })

    $("input[type=email]:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val().length < $(this).attr("minlength") || !is_email($(this).val())){
                inputs.push($(this).attr("name"))
            }
        }
    })

    $(".cpfCnpj input:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val().length < $(this).attr("minlength") || !is_cpfcnpj($(this).val())){
                inputs.push($(this).attr("name"))
            }
        }
    })

    $(".data input:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val().length < $(this).attr("minlength")){
                inputs.push($(this).attr("name"))
            }
        }
    })

    $("select:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val() == ""){
                inputs.push($(this).attr("name"))
            }
        }
    })

    $("textarea:visible",form).each(function(){
        if(!$(this).hasClass("norequire")){
            if($(this).val() == ""){
                inputs.push($(this).attr("name"))
            }
        }
    })

    if(inputs.length > 0){
        $(inputs).each(function(index,val){
            $("input[name='"+val+"']").parent().find(".error-input").show()
            $("input[name='"+val+"']").parent().find("label").addClass("text-danger")
            $("input[name='"+val+"']").addClass("border-danger")
            //
            $("select[name='"+val+"']").parent().find(".error-input").show()
            $("select[name='"+val+"']").parent().find("label").addClass("text-danger")
            $("select[name='"+val+"']").addClass("border-danger")
            //
            $("textarea[name='"+val+"']").parent().find(".error-input").show()
            $("textarea[name='"+val+"']").parent().find("label").addClass("text-danger")
            $("textarea[name='"+val+"']").addClass("border-danger")
        })
        return false
    }
    return true
}
//

