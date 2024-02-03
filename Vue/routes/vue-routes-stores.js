let routes= [];
let routes_list= [];

import List from '../pages/stores/List.vue'
import Form from '../pages/stores/Form.vue'
import Item from '../pages/stores/Item.vue'

routes_list = {

    path: '/stores',
    name: 'stores.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'stores.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'stores.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

