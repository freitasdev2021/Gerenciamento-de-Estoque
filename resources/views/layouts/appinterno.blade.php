<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>FR Controller</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{asset('img/fricon.ico')}}" />
        <!-- Core theme CSS (includes Bootstrap)-->
         <!--jQuery-->
         <link href="{{asset('css/style.css')}}" rel="stylesheet"/>
         <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
         <!--LOAD-->
         <link rel="stylesheet" href="{{asset('resources/css/libs/load.css')}}">
         <link rel="stylesheet" href="{{asset('css/lateralBar.css')}}">
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
         <!--DATATABLES-->
         <link href="{{asset('css/libs/datatables.css')}}" rel="stylesheet"/>
         <link rel="stylesheet" href="{{asset('css/libs/responsive.bootstrap4.min.css')}}">
         @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app">
        <div class="d-flex" id="wrapper">
            <!-- Sidebar-->
            <div class="border-end" id="sidebar-wrapper" style="background:#234D9D;">
                <div class="sidebar-heading text-white" style="background:#234D9D;">
                aaaa
                </div>
                <div class="list-group list-group-flush navegacao">
                    <a class="list-group-item list-group-item-action p-3 produtos border-bottom white" href="" style="cursor:pointer;"><i class="fa-solid fa-shop"></i>&nbsp;Produtos</a>
                    <a class="list-group-item list-group-item-action p-3 produtos border-bottom white" href="" style="cursor:pointer;"><i class="fa-solid fa-shop"></i>&nbsp;Produtos</a>
                    <a class="list-group-item list-group-item-action p-3 produtos border-bottom white" href="" style="cursor:pointer;"><i class="fa-solid fa-shop"></i>&nbsp;Produtos</a>
                </div>
            </div>
            <!-- Page content wrapper-->
            <div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light border-bottom" style="background:#234D9D;">
                    <div class="container-fluid" >
                        <button class="btn btn-light" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                                <li class="nav-item active"><a class="nav-link text-white" href="">Home</a></li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-white" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Usuário</a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item " href="#!">Ajuda</a>
                                        <a class="dropdown-item " href="#!">Suporte</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item " href="index.php?sair">Sair</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <!-- Page content-->
                <!-- <div class="logoCentral">
                    <img src="{{asset('img/logo.png" width="500px" height="250px">
                </div> -->
                <div class="container-fluid conteudo">
                    @yield('content')
                </div>
                
            </div>
            
        </div>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{asset('js/jquery.min.js"></script>
        <script src="{{asset('js/libs/maskmoney.js"></script>
        <script src="{{asset('js/scripts.js"></script>
        <script src="{{asset('js/libs/jquery.mask.js"></script>
        <script src="{{asset('js/libs/responsive.bootstrap4.min.js"></script>
        <script src="{{asset('js/libs/datatables.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
        <script src="{{asset('js/relatoriosfil.js"></script>
        </div>
    </body>
</html>