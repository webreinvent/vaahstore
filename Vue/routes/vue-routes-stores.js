import StoresList from "./../pages/stores/List";
import StoresCreate from "../pages/stores/Create";
import StoresView from "./../pages/stores/View";
import StoresEdit from "./../pages/stores/Edit";

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
                    component: StoresCreate,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'view/:id',
                    name: 'stores.view',
                    component: StoresView,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'edit/:id',
                    name: 'stores.edit',
                    component: StoresEdit,
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
