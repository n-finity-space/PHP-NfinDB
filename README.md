# NfinDB
an advanced schemaless datastore based on sqlite built in PHP .

# Quick overview

```php
<?php

  require "NfinDB.php";

  $db = new NfinDB("./data.db", "db");

  $db->putItem("global", "category-1", "post-slug-1", [
    "title" => "this is the title",
    "summary" => "the post summary"
  ]);

  $post = $db->getItem("global", "category-1", "post-slug-1");
  // fetch title: $post["title"], ... and so on

  $db->putItem("global", "categories", "category-1", [
    "title" => "...",
    // ...
  ]);

  $db->putItem("global", "mixed", "settings", [
   // ... etc
  ]);

  // ... etc
  // see the source code for more info
```
