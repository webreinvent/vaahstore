import BrandsList from "./../pages/brands/List";
import BrandsView from "./../pages/brands/View";
import Form from "./../pages/brands/Form";

import GetAssets from './middleware/GetAssets'


import Layout from "./../layouts/Backend";


let routes_brands=[];

let list =     {
    path: '/brands',
    component: Layout,
    props: true,
    meta: {
        middleware: [
            GetAssets
        ]
    },
    children: [
        {
            path: '/',
            name: 'brands.list',
            component: BrandsList,
            props: true,
            meta: {
                middleware: [
                    GetAssets
                ]
            },
            children: [
                {
                    path: 'create',
                    name: 'brands.create',
                    component: Form,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'read/:id',
                    name: 'brands.read',
                    component: BrandsView,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'update/:id',
                    name: 'brands.update',
                    component: Form,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                }

            ]
        }

    ]
};


routes_brands.push(list);

export default routes_brands;
