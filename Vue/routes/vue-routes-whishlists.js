let routes= [];
let routes_list= [];

import List from '../pages/whishlists/List.vue'
import Form from '../pages/whishlists/Form.vue'
import Item from '../pages/whishlists/Item.vue'

routes_list = {

    path: '/whishlists',
    name: 'whishlists.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'whishlists.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'whishlists.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

