<?php

namespace VaahCms\Modules\Store\Traits;

trait HasTransformer
{
    protected function getTransformer()
    {
        $frontend_ui = request()->get('frontend_ui') ?? config('store.frontendlibrary');

        if (!$frontend_ui) {
            return null;
        }
        $transformer_class = 'VaahCms\\Modules\\Store\\Libraries\\Transformer' . ucfirst($frontend_ui);
        if (class_exists($transformer_class)) {
            return $transformer_class;
        }

        return null;
    }
}
