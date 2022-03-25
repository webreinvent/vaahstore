let routes=[];

import dashboard from "./routes-dashboard";
import stores from "./vue-routes-stores";
import vendors from "./vue-routes-vendors";
import brands from "./vue-routes-brands";

routes = routes.concat(dashboard);
routes = routes.concat(stores);
routes = routes.concat(vendors);
routes = routes.concat(brands);

export default routes;
