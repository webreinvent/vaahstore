let routes= [];
let routes_list= [];

import List from '../pages/store/List.vue'
import Form from '../pages/store/Form.vue'
import Item from '../pages/store/Item.vue'

routes_list = {

    path: '/store',
    name: 'store.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'store.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'store.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

