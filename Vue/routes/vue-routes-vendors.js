import VendorsList from "./../pages/vendors/List";
import VendorsView from "./../pages/vendors/View";
import Form from "./../pages/vendors/Form";

import GetAssets from './middleware/GetAssets'


import Layout from "./../layouts/Backend";


let routes_vendors=[];

let list =     {
    path: '/vendors',
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
            name: 'vendors.list',
            component: VendorsList,
            props: true,
            meta: {
                middleware: [
                    GetAssets
                ]
            },
            children: [
                {
                    path: 'create',
                    name: 'vendors.create',
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
                    name: 'vendors.read',
                    component: VendorsView,
                    props: true,
                    meta: {
                        middleware: [
                            GetAssets
                        ]
                    },
                },
                {
                    path: 'update/:id',
                    name: 'vendors.update',
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


routes_vendors.push(list);

export default routes_vendors;
