@extends("vaahcms::backend.vaahone.layouts.backend")

@section('vaahcms_extend_backend_css')

@endsection


@section('vaahcms_extend_backend_js')
    <!--<script src="{{vh_module_assets_url("Store", "assets/js/script.js")}}"></script>-->
@endsection

@section('content')

    <!--sections-->
    <section class="section">
        <div class="container">
            <h1 class="title">Store</h1>
            <h2 class="subtitle">
                Your <strong>"Store"</strong> module's dashboard is ready!
            </h2>
        </div>
    </section>
    <!--sections-->



@endsection
