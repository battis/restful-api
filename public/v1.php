<?php

// TODO update with relative path from this file to src/api.php
/* The path here is relative from the _eventual_ location of this file
   in a web-served public folder to the _eventual_ location of the
   server code. By way of examples...

   If the relative paths are thus:

   - user home
     - server
       - src
         - api.v1.php
     - public_html
       - path
         - to
           - client
             - v1.php

    then the relative path would be __DIR__ . "/../../../../server/src/api.v1.php";
 */
require_once __DIR__ . "/../../server/src/api.v1.php";
