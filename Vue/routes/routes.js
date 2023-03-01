let routes= [];

import dashboard from "./vue-routes-dashboard";
import store from "./vue-routes-store";
import vendor from "./vue-routes-vendors";

routes = routes.concat(dashboard);
routes = routes.concat(store);
routes = routes.concat(vendor);

export default routes;
