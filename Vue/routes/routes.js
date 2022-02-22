let routes=[];

import dashboard from "./routes-dashboard";
import stores from "./vue-routes-stores";

routes = routes.concat(dashboard);
routes = routes.concat(stores);

export default routes;
