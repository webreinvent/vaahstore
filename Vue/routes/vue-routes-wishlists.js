let routes= [];
let routes_list= [];

import List from '../pages/wishlists/List.vue'
import Form from '../pages/wishlists/Form.vue'
import Item from '../pages/wishlists/Item.vue'
import Product from '../pages/wishlists/Product.vue'
routes_list = {

    path: '/wishlists',
    name: 'wishlists.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'wishlists.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'wishlists.view',
            component: Item,
            props: true,
        },
        {
            path: ':id?/product',
            name: 'wishlists.products',
            component: Product,
            props: true,
        }

    ]
};

routes.push(routes_list);

export default routes;

