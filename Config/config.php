<?php

return [
    "name"=> "Store",
    "title"=> "Ecommerce for VaahCMS",
    "slug"=> "store",
    "is_dev" => env('MODULE_STORE_ENV')?true:false,
    "thumbnail"=> "https://img.site/p/300/160",
    "excerpt"=> "Ecommerce for VaahCMS",
    "description"=> "Ecommerce for VaahCMS",
    "download_link"=> "",
    "author_name"=> "store",
    "author_website"=> "https://vaah.dev",
    "version"=> "0.0.43",
    "is_migratable"=> true,
    "is_sample_data_available"=> true,
    "db_table_prefix"=> "vh_store_",
    "providers"=> [
        "\\VaahCms\\Modules\\Store\\Providers\\StoreServiceProvider"
    ],
    "aside-menu-order"=> null
];
