let routes= [];

import dashboard from "./vue-routes-dashboard";
import store from "./vue-routes-store";

routes = routes.concat(dashboard);
routes = routes.concat(store);

export default routes;
