import ProductsList from "./../pages/products/List";
import ProductsView from "./../pages/products/View";
import Form from "./../pages/products/Form";
import Product from "./../pages/products/Product";

import GetAssets from './middleware/GetAssets'


import Layout from "./../layouts/Backend";


let routes_products=[];

let list =     {
    path: '/products',
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
            name: 'products.list',
            component: ProductsList,
            props: true,
            meta: {
                middleware: [
                    GetAssets
                ]
            },
            children: [
                {
                    path: 'create',
                    name: 'products.create',
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
                    name: 'products.read',
                    component: ProductsView,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'update/:id',
                    name: 'products.update',
                    component: Form,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                }

            ]
        },
        {
            path: 'product/:id',
            name: 'products.product',
            component: Product,
            props: true,
            meta: {
                middleware: [
                    GetAssets
                ]
            },
        },

    ]
};


routes_products.push(list);

export default routes_products;
