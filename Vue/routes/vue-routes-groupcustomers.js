let routes= [];
let routes_list= [];

import List from '../pages/groupcustomers/List.vue'
import Form from '../pages/groupcustomers/Form.vue'
import Item from '../pages/groupcustomers/Item.vue'

routes_list = {

    path: '/groupcustomers',
    name: 'groupcustomers.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'groupcustomers.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'groupcustomers.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

