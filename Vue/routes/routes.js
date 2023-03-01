let routes= [];

import dashboard from "./vue-routes-dashboard";
import vendor from "./vue-routes-vendors";

routes = routes.concat(dashboard);
routes = routes.concat(vendor);

export default routes;
