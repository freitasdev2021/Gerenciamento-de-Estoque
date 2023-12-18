

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
    //FUNÇÃO DOS GRAFICOS
    getProdutosVendidos()
    //AÇÃO DO REGEX DATA
    $(".data input").keyup(function(){
        $(this).val(formataData($(this).val()))
    })
    //AÇÃO BOTÃO DE ENVIAR FORMULARIO
    $("#formCategorias").on("submit",function(e){
        e.preventDefault()
        setCategoria("#formCategorias")
    })
    $("#formMovimentacao").on("submit",function(e){
        e.preventDefault()
        setMovimentacao("#formMovimentacao")
    })

    //AÇÃO DE EXCLUSÃO
    $(".btn-excluir-categoria").on("click",function(){
        excluirCategoria($(this).attr("data-id"),$(this).attr("data-csrf"),0)
    })
    $(".btn-excluir-produto").on("click",function(){
        if(confirm("Deseja Excluir esse Produto?")){
            excluirProduto($(this).attr("data-id"),$(this).attr("data-csrf"))
        }
    })
    $("#formProdutos").on("submit",function(e){
        e.preventDefault()
        setProduto("#formProdutos")
    })
    //HEADER DA TABELA
    $(".tabela thead tr th").css({
        background: '#234D9D',
        color: 'white',
        "text-align":"center"
    })
    //IMAGEM DO PRODUTO
    $("#fotoProduto").on("change",function(){
        // Receber o arquivo do formulario
        var receberArquivo = document.getElementById("fotoProduto").files;
        //console.log(receberArquivo);
    
        // Verificar se existe o arquivo
        if (receberArquivo.length > 0) {
    
            // Carregar a imagem
            var carregarImagem = receberArquivo[0];
            //console.log(carregarImagem);
    
            // FileReader - permite ler o conteudo do arquivo do computador do usuario
            var lerArquivo = new FileReader();
    
            // O evento onload ocorre quando um objeto he carregado
            lerArquivo.onload = function(arquivoCarregado) {
               var imagemBase64 = arquivoCarregado.target.result;  
               if(imagemBase64.length > 6993772){
                alert("Não e Permitido Imagens Maiores que 5mb")
               }else{
                $("#imagemProduto").attr("src",imagemBase64)
               }
            }  
    
            // O metodo readAsDataURL e usado para ler o conteudo
            lerArquivo.readAsDataURL(carregarImagem);
        }
    })
    //DATATABLES
    $(".tabela").DataTable({
        "responsive": true,
        "autoWidth": false,
        "bDestroy": true
    })
    //FORNATAR VALOR MONETÁRIO
    $(".money input").maskMoney({ 
        allowNegative: false,
        thousands:'.',
        decimal:',',
        affixesStay: true
    })
    //FORMATAR TEXTO SEM CARACTERES ESPECIAIS
    $('input[type=name]').bind('input',function(){
        str = $(this).val().replace(/[^A-Za-z\u00C0-\u00FF\-\/\s]+/g,'');
        str = str.replace(/[\s{ \2 }]+/g,' ');
        if(str == " ")str = "";
        $(this).val( str );
    });
    //FORMATAR TEXTAREA SEM CARACTERES ESPECIAIS
    $('textarea').bind('textarea',function(){
        str = $(this).val().replace(/[^A-Za-z\u00C0-\u00FF\-\/\s]+/g,'');
        str = str.replace(/[\s{ \2 }]+/g,' ');
        if(str == " ")str = "";
        $(this).val( str );
    });
    //EXCLUIR OS ERROS QUANDO RECARREGAR A TELA
    $(".error-input").hide()
})
//GERA OS GRÁFICOS
function getProdutosVendidos(){
    $.ajax({
        method : "POST",
        url    : "relatorios/graficos",
        headers : {
            'X-CSRF-TOKEN': $("#graficoProdutos").attr("data-csrf")
        }
    }).done(function(retorno){
        trintadias = jQuery.parseJSON(retorno)
        quantidade = [];  
        produtos = []
        trintadias.forEach((i)=>{
            produtos.push(i.NMProduto)
            quantidade.push(i.QTVenda)
        })
        var ctx = document.getElementById("graficoProdutos");
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: produtos,
                
                datasets: [{
                    label: 'Vendas',
                    fill:false,
                    data: quantidade,
                    backgroundColor: 'blue',
                    borderWidth: 1
                }
              ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true,
                            callback : (value,index,values) => {
                                return value
                            }
                        }
                    }]
                },
                title: {
                    display: true,
                    text: 'Top 10 Maiores Saidas'
                },
                responsive: true,
                
              tooltips: {
                    callbacks: {
                        labelColor: function(tooltipItem, chart) {
                            return {
                                borderColor: 'green',
                                backgroundColor: 'blue'
                            }
                        },
                        label : function(tooltipItem,data) {
                            return trataValor(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index],0)
                        }
                    }
                },
                legend: {
                    labels: {
                        // This more specific font property overrides the global property
                        fontColor: 'black',
                      
                    }
                }
            }
        });  
    })
}

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
//FORMATAR DATA
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
//EXCLUIR CATEGORIA
function excluirCategoria(id,csrf,confirmacao){
    // console.log(confirmacao)
    // return false
    $.ajax({
        method : "POST",
        url : 'categorias/delete',
        data : {
            IDCategoria : id,
            confirmar : confirmacao
        },
        headers: {
            'X-CSRF-TOKEN': csrf
        }
    }).done(function(retornoCat){
       var categoria = jQuery.parseJSON(retornoCat)
       if(confirmacao == 0){
        if(categoria.status){
            if(confirm(categoria.mensagem)){
                excluirCategoria(id,csrf,1)
            }
           }else{
            alert(categoria.mensagem)
           }
       }else{
        window.location.reload()
       }
    })
}
//EXCLUIR PRODUTO
function excluirProduto(id,csrf){
    $.ajax({
        method : "POST",
        url : 'produtos/delete',
        data : {
            IDProduto : id
        },
        headers: {
            'X-CSRF-TOKEN': csrf
        }
    }).done(function(){
        window.location.reload()
    })
}
//ENVIAR DADOS DOS PRODUTOS
function setProduto(form){
    if($("input[name=idpro]",form).val()){
        rota = "update"
    }else{
        rota = "set"
    }
    if(!validaCampos(form)){
        return false
    }
    $.ajax({
        method : "POST",
        url    : rota,
        data : {
            idProduto         : $("input[name=idpro]",form).val(),
            nomeProduto       : $("input[name=nomeProduto]",form).val(),
            skuProduto        : $("input[name=skuProduto]",form).val(),
            descricaoProduto  : $("textarea[name=descricaoProduto]",form).val(),
            valorProduto      : trataValor($("input[name=valorProduto]",form).val(),1),
            categoriaProduto  : $("select[name=categoriaProduto]",form).val(),
            estoqueProduto    : $("input[name=estoqueProduto]",form).val(),
            vencimentoProduto : $("input[name=vencimentoProduto]",form).val(),
            imagemProduto     : $("#imagemProduto").attr("src")
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }).done(function(retorno){
        var rt = jQuery.parseJSON(retorno);
        alert(rt.mensagem);
        if(rt.status){
            var urlAtual = window.location.href;
            if($("input[name=idpro]",form).val()){
                window.location.href=urlAtual.replace("/editar/"+$("input[name=idpro]",form).val()," ")
            }else{
                window.location.href=urlAtual.replace("/create"," ")
            }
            
        }
    })
}
//ENVIAR DADOS DA MOVIMENTAÇÃO
function setMovimentacao(form){
    if(!validaCampos(form)){
        return false
    }
    $.ajax({
        method : "POST",
        url    : 'set',
        data : {
            quantidadeProduto : $("input[name=quantidadeProduto]",form).val(),
            tipoMovimentacao  : $("select[name=tipoMovimentacao]",form).val(),
            valorMovimentacao : $("input[name=valorMovimentacao]",form).val(),
            idProduto         : $("select[name=produtoMovimentacao]",form).val(),
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }).done(function(retorno){
        // console.log(retorno)
        // return false
        var rt = jQuery.parseJSON(retorno);
        alert(rt.mensagem);
        if(rt.status){
            var urlAtual = window.location.href;
            window.location.href=urlAtual.replace("/create"," ")
        }
    })
}

//ENVIAR DADOS DAS CATEGORIAS
function setCategoria(form){
    if($("input[name=idcat]",form).val()){
        rota = "update"
    }else{
        rota = "set"
    }
    if(!validaCampos(form)){
        return false
    }
    $.ajax({
        method : "POST",
        url    : rota,
        data : {
            idCategoria : $("input[name=idcat]",form).val(),
            nomeCategoria : $("input[name=nomeCategoria]",form).val()
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }).done(function(retorno){
        var rt = jQuery.parseJSON(retorno);
        alert(rt.mensagem);
        if(rt.status){
            var urlAtual = window.location.href;
            if($("input[name=idcat]",form).val()){
                window.location.href=urlAtual.replace("/editar/"+$("input[name=idcat]",form).val()," ")
            }else{
                window.location.href=urlAtual.replace("/create"," ")
            }
        }
    })
}

