let routes= [];

import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";

routes = routes.concat(dashboard);
routes = routes.concat(brand);

export default routes;
