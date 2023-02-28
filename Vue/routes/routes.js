let routes= [];

import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";
import store from "./vue-routes-store";

routes = routes.concat(dashboard);
routes = routes.concat(brand);
routes = routes.concat(store);

export default routes;
