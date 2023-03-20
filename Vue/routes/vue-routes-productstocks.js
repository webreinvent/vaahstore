let routes= [];
let routes_list= [];

import List from '../pages/productstocks/List.vue'
import Form from '../pages/productstocks/Form.vue'
import Item from '../pages/productstocks/Item.vue'

routes_list = {

    path: '/productstocks',
    name: 'productstocks.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'productstocks.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productstocks.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

