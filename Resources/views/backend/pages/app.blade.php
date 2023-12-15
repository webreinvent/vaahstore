@extends(((version_compare(config('vaahcms.version'), '2.0.0', '<' )) ? 'vaahcms::backend.vaahone.layouts.backend' : 'vaahcms::backend.vaahtwo.layouts.backend' ))

@section('vaahcms_extend_backend_css')
    @if(env('MODULE_STORE_ENV') !== 'develop')
        <link href="{{vh_module_assets_url("Store", "build/index.css")}}"
              rel="stylesheet" media="screen"></link>
    @endif
@endsection


@section('vaahcms_extend_backend_js')

    @if(env('MODULE_STORE_ENV') == 'develop')
        <script type="module" src="http://localhost:8464/Vue/main.js"></script>
    @else
        <script type="module" src="{{vh_module_assets_url("Store", "build/index.js")}}"></script>
    @endif

@endsection

@section('content')

    <div class="primevue">
        <div id="appStore">


        </div>
    </div>

@endsection
