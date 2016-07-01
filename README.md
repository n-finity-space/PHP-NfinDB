# NfinDB
an advanced schemaless datastore based on sqlite built in PHP .

# Concept
NfinDB is built on the `key->value` store concept but developed to be `namespace->type->key->value`, 
each `namespace` may be like `Database`, `type` = `Table`, and `key->value` = `Row` .

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
  // or
  $post = $db->findItem("post-slug-1");
  // fetch title: $post["title"], ... and so on

  $db->putItem("global", "categories", "category-1", [
    "title" => "...",
    // ...
  ]);

  $db->putItem("global", "mixed", "settings", [
   // ... etc
  ]);

  // fetch all namespaces
  $db->getNamespaces();

  // fetch all types of a namespace called "global"
  $db->getTypes("global");

  // fetch all items in namespace "global" and type "categories"
  $db->getItems("global", "categories");

  // ... etc
  // see the source code for more info
```
