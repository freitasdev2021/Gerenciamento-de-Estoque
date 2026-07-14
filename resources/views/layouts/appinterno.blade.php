<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>FR Controller</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{asset('img/fricon.ico')}}" />
        <!-- Core theme CSS (includes Bootstrap)-->
         <!--jQuery-->
         <link href="{{asset('css/style.css')}}" rel="stylesheet"/>
         <link href="{{asset('css/libs/styles.css')}}" rel="stylesheet"/>
         <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
         <!--LOAD-->
         <link rel="stylesheet" href="{{asset('css/libs/load.css')}}">
         <link rel="stylesheet" href="{{asset('css/lateralBar.css')}}">
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
         <!--DATATABLES-->
         <link href="{{asset('css/libs/datatables.css')}}" rel="stylesheet"/>
         <link rel="stylesheet" href="{{asset('css/libs/responsive.bootstrap4.min.css')}}">
         @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="d-flex" id="wrapper">
            <!-- Sidebar-->
            <div id="sidebar-wrapper">
                <div class="sidebar-heading">
                    <div class="user-avatar">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-welcome">Olá,</span>
                        <span class="user-name">{{Auth::user()->name}}</span>
                    </div>
                </div>
                
                <div class="list-group list-group-flush navegacao">
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'produtos')) ? 'active' : '' }}" href="{{route('produtos.index')}}">
                        <i class="fa-solid fa-shop"></i><span>Produtos</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'clientes')) ? 'active' : '' }}" href="{{route('clientes.index')}}">
                        <i class="fa-solid fa-users"></i><span>Clientes</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'categorias')) ? 'active' : '' }}" href="{{route('categorias.index')}}">
                        <i class="fa-solid fa-list"></i><span>Categorias</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'fornecedores')) ? 'active' : '' }}" href="{{route('fornecedores.index')}}">
                        <i class="fa-solid fa-truck"></i><span>Fornecedores</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'pagamentos')) ? 'active' : '' }}" href="{{route('pagamentos.index')}}">
                        <i class="fa-solid fa-credit-card"></i><span>Pagamentos</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'reposicoes')) ? 'active' : '' }}" href="{{route('reposicoes.index')}}">
                        <i class="fa-solid fa-boxes-stacked"></i><span>Reposições</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'promocoes')) ? 'active' : '' }}" href="{{route('promocoes.index')}}">
                        <i class="fa-solid fa-tag"></i><span>Promoções</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ (Route::currentRouteName() && str_contains(Route::currentRouteName(), 'movimentacoes')) ? 'active' : '' }}" href="{{route('movimentacoes.index')}}">
                        <i class="fa-solid fa-box"></i><span>Estoque</span>
                    </a>
                    <a class="list-group-item list-group-item-action {{ Route::currentRouteName() == 'relatorios.index' ? 'active' : '' }}" href="{{route('relatorios.index')}}">
                        <i class="fa-solid fa-chart-simple"></i><span>Relatório</span>
                    </a>
                </div>
            </div>

            <!-- Page content wrapper-->
            <div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <button class="btn btn-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                        
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link btn-logout" href="" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket"></i> Sair
                                    </a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </nav>

                <!-- Conteúdo Principal -->
                <div class="container-fluid conteudo-principal">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="page-card">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </body>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/libs/maskmoney.js')}}"></script>
    <script src="{{asset('js/scripts.js')}}"></script>
    <script src="{{asset('js/libs/jquery.mask.js')}}"></script>
    <script src="{{asset('js/libs/responsive.bootstrap4.min.js')}}"></script>
    <script src="{{asset('js/libs/datatables.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
     @stack('scripts')
</html>
