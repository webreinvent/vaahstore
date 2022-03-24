@extends("vaahcms::backend.vaahone.layouts.backend")

@section('vaahcms_extend_backend_css')

@endsection


@section('vaahcms_extend_backend_js')

    @if(env('MODULE_STORE_ENV') == 'develop')
        <script src="http://localhost:9060/store/assets/build/app.js" defer></script>
    @else
        <script src="{{vh_module_assets_url("Store", "build/app.js")}}"></script>
    @endif

@endsection

@section('content')

    <div id="appStore">

        <router-view></router-view>

        <vue-progress-bar></vue-progress-bar>

    </div>

@endsection
