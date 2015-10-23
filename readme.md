Magento Cleanup
==============

Simple script which iterates all databases on the given server and performs cleanup of all Magento temporary and log data.

Usage:

```sh
php magentoCleanup.php
```

Please edit server parameters at the begining of file

```sh
const DB_HOST = '127.0.0.1';
const DB_USERNAME = 'username';
const DB_PASSWORD = 'passwors';
```

Tables being truncated:
```sh
dataflow_batch_export
dataflow_batch_import
log_customer
log_quote
log_summary
log_summary_type
log_url
log_url_info
log_visitor
log_visitor_info
log_visitor_online
report_viewed_product_index
report_compared_product_index
report_event
index_event
catalog_compare_item
```
