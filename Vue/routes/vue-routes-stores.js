import StoresList from "./../pages/stores/List";
import StoresView from "./../pages/stores/View";
import Form from "./../pages/stores/Form";

import GetAssets from './middleware/GetAssets'

import LayoutBackend from "./../layouts/Backend";

let routes_stores=[];

let list =     {
    path: '/stores',
    component: LayoutBackend,
    props: true,
    meta: {
        middleware: [
            GetAssets
        ]
    },
    children: [
        {
            path: '/',
            name: 'stores.list',
            component: StoresList,
            props: true,
            meta: {
                middleware: [
                    GetAssets
                ]
            },
            children: [
                {
                    path: 'create',
                    name: 'stores.create',
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
                    name: 'stores.read',
                    component: StoresView,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'update/:id',
                    name: 'stores.update',
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


routes_stores.push(list);

export default routes_stores;
