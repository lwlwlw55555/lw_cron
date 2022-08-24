create table app
(
    id                  mediumint unsigned auto_increment
        primary key,
    app_type            varchar(30)  null,
    platform_app_key    varchar(128) not null comment '平台应用id',
    platform_app_secret varchar(64)  null comment '平台秘钥',
    constraint app_type
        unique (app_type),
    constraint app_type_key
        unique (app_type, platform_app_key)
)
    comment 'app表' charset = utf8;

create table config
(
    config_id    int(10) auto_increment
        primary key,
    config_key   varchar(30)          null,
    config_value varchar(100)         null,
    config_desc  varchar(30)          null,
    deleted      tinyint(1) default 0 null comment '0 未删除     1 删除',
    constraint config_key
        unique (config_key)
)
    charset = utf8;

create table facility
(
    facility_id                int unsigned auto_increment
        primary key,
    party_id                   mediumint unsigned                                                                not null,
    is_multi_warehouse         tinyint(1)          default 0                                                     null,
    default_warehouse_id       int(10)                                                                           null,
    facility_name              varchar(120)        default ''                                                    not null,
    enabled                    tinyint(1) unsigned default 0                                                     not null,
    facility_code              varchar(30)         default ''                                                    not null comment '仓库code',
    facility_title             varchar(30)                                                                       null comment '面单抬头',
    province_id                int(10)                                                                           null,
    province_name              varchar(30)                                                                       null,
    city_id                    int(10)                                                                           null,
    city_name                  varchar(30)                                                                       null,
    district_id                int(10)                                                                           null,
    district_name              varchar(30)                                                                       null,
    shipping_address           varchar(30)                                                                       null comment '真实地址',
    postcode                   varchar(8)                                                                        null comment '邮编',
    sender_mobile              varchar(30)                                                                       null comment '发件人电话',
    sender_name                varchar(30)                                                                       null comment '发件人',
    sender_company             varchar(30)                                                                       null,
    is_pre_shipping            int(10)             default 0                                                     null comment '0-不预发货，1-预发货',
    is_force_manage_goods      int(10)             default 0                                                     null comment '是否强管理商品',
    same_as_print              tinyint(1)          default 1                                                     null,
    is_notify_shipped          tinyint(1)          default 1                                                     not null comment '1 发货通知，0 不通知',
    is_best_shipping           tinyint(1)          default 0                                                     null,
    is_tactics                 tinyint(1)          default 0                                                     null,
    is_sync_all_inventory      tinyint(1)          default 0                                                     null,
    used_service               varchar(256)                                                                      null comment '使用中的功能，gift',
    best_shipping              varchar(64)                                                                       null comment 'region表示只开了地址，goods表示只开了商品，goods,region表示都开了goods优先,region,goods表示都开了region优先； is_best_shipping这个字段弃用',
    best_shipping_refresh_time datetime                                                                          null comment '智能快递最后刷新时间',
    is_auto_cancel_send        tinyint(1)          default 0                                                     null comment '是否自动取消发货',
    order_flag_for_report      varchar(1024)       default '0,1,2,3,4,5'                                         null,
    order_flag_map             varchar(1024)       default '{"1":"类型1","2":"类型2","3":"类型3","4":"类型4","5":"类型5"}' null
)
    charset = utf8;

create index default_warehouse_id
    on facility (default_warehouse_id);

create index is_force_manage_goods
    on facility (is_force_manage_goods);

create index is_multi_warehouse
    on facility (is_multi_warehouse);

create index is_tactics
    on facility (is_tactics);

create index party_id
    on facility (party_id);

create table facility_address
(
    facility_address_id int unsigned auto_increment
        primary key,
    facility_id         int unsigned                        not null,
    warehouse_id        int(10)                             null,
    province_id         int(10)                             null,
    province_name       varchar(30)                         null,
    city_id             int(10)                             null,
    city_name           varchar(30)                         null,
    district_id         int(10)                             null,
    district_name       varchar(30)                         null,
    shipping_address    varchar(30)                         null comment '真实地址',
    postcode            varchar(8)                          null comment '邮编',
    sender_mobile       varchar(30)                         null comment '发件人电话',
    sender_name         varchar(30)                         null comment '发件人',
    sender_company      varchar(30)                         null,
    is_default          int(10)   default 0                 null,
    created_time        datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time   timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on facility_address (created_time);

create index facility_id
    on facility_address (facility_id);

create index is_default
    on facility_address (is_default);

create index warehouse_id
    on facility_address (warehouse_id);

create table facility_best_shipping_goods
(
    facility_best_shipping_goods_id int unsigned auto_increment
        primary key,
    facility_id                     int(10)                                   not null,
    warehouse_id                    int(10)                                   null,
    platform_goods_id               bigint unsigned                           not null,
    platform_sku_id                 bigint unsigned default 0                 not null,
    shipping_id                     int(10)                                   not null,
    created_time                    datetime        default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time               timestamp       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_goods
        unique (facility_id, warehouse_id, platform_goods_id, platform_sku_id)
)
    charset = utf8;

create index platform_goods_id
    on facility_best_shipping_goods (platform_goods_id);

create index platform_sku_id
    on facility_best_shipping_goods (platform_sku_id);

create index warehouse_id
    on facility_best_shipping_goods (warehouse_id);

create table facility_best_shipping_goods_history
(
    facility_best_shipping_goods_history_id int unsigned auto_increment
        primary key,
    facility_best_shipping_goods_id         int unsigned                        null,
    facility_id                             int(10)                             not null,
    warehouse_id                            int(10)                             null,
    shop_id                                 int(10)                             not null,
    platform_goods_id                       bigint unsigned                     null,
    platform_sku_id                         bigint unsigned                     null,
    shipping_id                             int(10)                             not null,
    created_time                            datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index facility_id
    on facility_best_shipping_goods_history (facility_id);

create index warehouse_id
    on facility_best_shipping_goods_history (warehouse_id);

create table facility_best_shipping_region
(
    facility_best_shipping_region_id int unsigned auto_increment
        primary key,
    facility_id                      int(10)                             not null,
    warehouse_id                     int(10)                             null,
    province_id                      smallint unsigned                   not null,
    city_id                          smallint unsigned                   null,
    district_id                      smallint unsigned                   null,
    shipping_id                      int(10)                             not null,
    created_time                     datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_region
        unique (facility_id, warehouse_id, province_id, city_id, district_id)
)
    charset = utf8;

create index city_id
    on facility_best_shipping_region (city_id);

create index district_id
    on facility_best_shipping_region (district_id);

create index facility_id
    on facility_best_shipping_region (facility_id);

create index province_id
    on facility_best_shipping_region (province_id);

create index warehouse_id
    on facility_best_shipping_region (warehouse_id);

create table facility_best_shipping_region_history
(
    facility_best_shipping_region_history_id int unsigned auto_increment
        primary key,
    facility_id                              int(10)                             not null,
    warehouse_id                             int(10)                             null,
    province_id                              smallint unsigned                   not null,
    city_id                                  smallint unsigned                   null,
    district_id                              smallint unsigned                   null,
    shipping_id                              int(10)                             not null,
    created_time                             datetime  default CURRENT_TIMESTAMP null,
    back_created_time                        datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                        timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index facility_id
    on facility_best_shipping_region_history (facility_id);

create index warehouse_id
    on facility_best_shipping_region_history (warehouse_id);

create table facility_bill_template
(
    facility_id              int unsigned                                  not null,
    warehouse_id             int(10)             default 0                 not null,
    is_show_bill_name        tinyint(1) unsigned default 0                 not null,
    is_show_order_sn         tinyint(1) unsigned default 1                 not null,
    is_show_confirm_time     tinyint(1) unsigned default 1                 not null,
    is_show_outer_goods_id   tinyint(1) unsigned default 0                 not null,
    is_show_seller_note      tinyint(1) unsigned default 1                 not null,
    is_show_buyer_note       tinyint(1) unsigned default 1                 not null,
    is_show_receive_name     tinyint(1) unsigned default 1                 not null,
    is_show_mobile           tinyint(1) unsigned default 1                 not null,
    is_show_address          tinyint(1) unsigned default 1                 not null,
    is_show_number_id        tinyint(1) unsigned default 0                 not null,
    is_show_image_url        tinyint(1) unsigned default 0                 not null,
    is_show_goods_name       tinyint(1) unsigned default 1                 not null,
    is_show_style_value      tinyint(1) unsigned default 1                 not null,
    is_show_outer_id         tinyint(1) unsigned default 0                 not null,
    is_show_goods_price      tinyint(1) unsigned default 1                 not null,
    is_show_goods_number     tinyint(1) unsigned default 1                 not null,
    is_show_multi_goods      tinyint(1) unsigned default 0                 not null,
    is_show_gift_goods       tinyint(1) unsigned default 0                 not null,
    is_show_sum_goods_number tinyint(1) unsigned default 0                 not null,
    is_show_shipping_amount  tinyint(1) unsigned default 0                 not null,
    is_show_pay_amount       tinyint(1) unsigned default 0                 not null,
    is_show_goods_amount     tinyint(1) unsigned default 0                 not null,
    is_show_sender_mobile    tinyint(1) unsigned default 1                 not null,
    is_show_sender_name      tinyint(1) unsigned default 0                 not null,
    is_show_warehouse_name   tinyint(1) unsigned default 0                 not null,
    is_show_facility_address tinyint(1) unsigned default 1                 not null,
    normal_front_size        int(10)             default 14                null comment '字体大小',
    paper_size               varchar(30)         default 'A4*140*210'      null,
    custom_locale_list       text                                          null,
    image_list               text                                          null,
    created_time             datetime            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time        timestamp           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    is_show_shop_name        tinyint(1) unsigned default 1                 not null,
    primary key (facility_id, warehouse_id)
)
    charset = utf8;

create index is_show_goods_name
    on facility_bill_template (is_show_goods_name);

create index is_show_goods_number
    on facility_bill_template (is_show_goods_number);

create index is_show_seller_note
    on facility_bill_template (is_show_seller_note);

create index is_show_style_value
    on facility_bill_template (is_show_style_value);

create index warehouse_id
    on facility_bill_template (warehouse_id);

create table facility_copy1
(
    facility_id                int unsigned auto_increment
        primary key,
    party_id                   mediumint unsigned                                                                                                                 not null,
    is_multi_warehouse         tinyint(1)          default 0                                                                                                      null,
    default_warehouse_id       int(10)                                                                                                                            null,
    facility_name              varchar(120)        default ''                                                                                                     not null,
    enabled                    tinyint(1) unsigned default 0                                                                                                      not null,
    facility_code              varchar(30)         default ''                                                                                                     not null comment '仓库code',
    facility_title             varchar(30)                                                                                                                        null comment '面单抬头',
    province_id                int(10)                                                                                                                            null,
    province_name              varchar(30)                                                                                                                        null,
    city_id                    int(10)                                                                                                                            null,
    city_name                  varchar(30)                                                                                                                        null,
    district_id                int(10)                                                                                                                            null,
    district_name              varchar(30)                                                                                                                        null,
    shipping_address           varchar(30)                                                                                                                        null comment '真实地址',
    postcode                   varchar(8)                                                                                                                         null comment '邮编',
    sender_mobile              varchar(30)                                                                                                                        null comment '发件人电话',
    sender_name                varchar(30)                                                                                                                        null comment '发件人',
    sender_company             varchar(30)                                                                                                                        null,
    is_pre_shipping            int(10)             default 0                                                                                                      null comment '0-不预发货，1-预发货',
    is_force_manage_goods      int(10)             default 0                                                                                                      null comment '是否强管理商品',
    order_by_column            varchar(50)         default 'shipping_due_time'                                                                                    null comment '默认排序',
    is_desc                    tinyint(1)          default 0                                                                                                      null,
    inventory_order_by_column  varchar(50)         default 'created_time'                                                                                         null comment '默认排序',
    inventory_is_desc          tinyint(1)          default 1                                                                                                      null,
    same_as_print              tinyint(1)          default 1                                                                                                      null,
    is_notify_shipped          tinyint(1)          default 1                                                                                                      not null comment '1 发货通知，0 不通知',
    is_best_shipping           tinyint(1)          default 0                                                                                                      null,
    is_tactics                 tinyint(1)          default 0                                                                                                      null,
    is_sync_all_inventory      tinyint(1)          default 0                                                                                                      null,
    used_service               varchar(256)                                                                                                                       null comment '使用中的功能，gift',
    best_shipping              varchar(64)                                                                                                                        null comment 'region表示只开了地址，goods表示只开了商品，goods,region表示都开了goods优先,region,goods表示都开了region优先； is_best_shipping这个字段弃用',
    best_shipping_refresh_time datetime                                                                                                                           null comment '智能快递最后刷新时间',
    user_table_display         varchar(512)        default '{"receive_name":1,"address":1,"mobile":0,"shop_name":0,"order_sn":0,"buyer_nick":0,"confirm_time":0}' null,
    is_auto_cancel_send        tinyint(1)          default 0                                                                                                      null comment '是否自动取消发货',
    order_flag_for_report      varchar(1024)       default '0,1,2,3,4,5'                                                                                          null,
    order_flag_map             varchar(1024)       default '{"1":"类型1","2":"类型2","3":"类型3","4":"类型4","5":"类型5"}'                                                  null,
    pre_ship_order_by_column   varchar(50)         default 'pre_shipping_time'                                                                                    null comment '预发货默认排序',
    pre_ship_is_desc           tinyint(1)          default 1                                                                                                      null
)
    charset = utf8;

create index default_warehouse_id
    on facility_copy1 (default_warehouse_id);

create index is_force_manage_goods
    on facility_copy1 (is_force_manage_goods);

create index is_multi_warehouse
    on facility_copy1 (is_multi_warehouse);

create index is_tactics
    on facility_copy1 (is_tactics);

create index party_id
    on facility_copy1 (party_id);

create table facility_oauth
(
    facility_oauth_id int unsigned auto_increment comment 'id'
        primary key,
    facility_id       int unsigned                        not null,
    platform_name     varchar(30)                         not null comment '平台',
    oauth_id          int unsigned                        null,
    created_time      datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_platform_name
        unique (facility_id, platform_name)
)
    charset = utf8;

create index created_time
    on facility_oauth (created_time);

create index last_updated_time
    on facility_oauth (last_updated_time);

create index oauth_id
    on facility_oauth (oauth_id);

create table facility_shiping_back_20200422
(
    facility_shipping_id       int unsigned auto_increment
        primary key,
    facility_id                int(10)                                not null,
    warehouse_id               int(10)                                null,
    shipping_id                int(10)                                not null,
    is_cainiao_thermal         tinyint(1)   default 0                 null,
    is_pdd_thermal             tinyint(1)   default 0                 null,
    is_kuaidi_thermal          tinyint(1)   default 0                 null,
    default_thermal_type       varchar(30)                            not null comment 'PDD,CAINIAO,EXPRESS',
    enable                     tinyint(1)   default 1                 not null comment '1-可用，0-不可用',
    send_addr_code             varchar(30)                            null comment '顺丰原寄地',
    sf_account                 varchar(12)                            null,
    facility_shipping_name     varchar(32)                            null comment '快递名称',
    facility_shipping_user     varchar(32)                            null,
    facility_shipping_password varchar(64)                            null,
    facility_shipping_site     varchar(60)                            null comment '申通网点或顺丰模板',
    facility_shipping_account  varchar(32)                            null comment '月结账号(仅顺丰必填)',
    logistic_service_id        int                                    null comment '快递服务',
    pay_method                 varchar(20)                            null comment '付费方式',
    service_type               varchar(30)  default ''                null comment '顺丰服务类型',
    service_type_code          int(10)      default 0                 null comment '顺丰服务类型编码',
    sort                       int          default 1                 not null,
    cainiao_oauth_id           int unsigned                           null,
    cainiao_branch_code        varchar(128) default ''                not null comment '发件网点code',
    cainiao_branch_name        varchar(128) default ''                not null comment '发件网点名称',
    cainiao_branch_address     varchar(512) default ''                not null comment '发货地',
    cainiao_template_id        int unsigned                           null comment '面单模板id',
    pdd_oauth_id               int unsigned                           null,
    pdd_branch_code            varchar(128) default ''                not null comment '发件网点code',
    pdd_branch_name            varchar(128) default ''                not null comment '发件网点名称',
    pdd_branch_address         varchar(512) default ''                not null comment '发货地',
    pdd_template_id            int unsigned                           null comment '面单模板id',
    kuaidi_template_id         int unsigned                           null comment '面单模板id',
    shipping_fee_template_id   int unsigned                           null comment '运费模板id',
    created_time               datetime     default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time          timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    logistic_service           varchar(512)                           null comment '拼多多增值服务',
    constraint unique_fws_id
        unique (facility_id, warehouse_id, shipping_id)
)
    charset = utf8;

create index cainiao_oauth_id
    on facility_shiping_back_20200422 (cainiao_oauth_id);

create index cainiao_template_id
    on facility_shiping_back_20200422 (cainiao_template_id);

create index enable
    on facility_shiping_back_20200422 (enable);

create index facility
    on facility_shiping_back_20200422 (facility_id);

create index kuaidi_template_id
    on facility_shiping_back_20200422 (kuaidi_template_id);

create index pdd_oauth_id
    on facility_shiping_back_20200422 (pdd_oauth_id);

create index pdd_template_id
    on facility_shiping_back_20200422 (pdd_template_id);

create index shipping_id
    on facility_shiping_back_20200422 (shipping_id);

create index warehouse_id
    on facility_shiping_back_20200422 (warehouse_id);

create table facility_shipment_flag
(
    facility_shipment_flag_id   int unsigned auto_increment
        primary key,
    facility_shipment_flag_name varchar(64)                                                   not null,
    facility_id                 int(10)                                                       not null,
    is_sync_flag                tinyint(1)                          default 0                 null comment '是否同步标记，sync只看等于1的',
    is_auto_cancel              tinyint(1)                          default 0                 null comment '同步时为1把shipment的status改为CANCEL',
    shop_ids                    varchar(512)                                                  null,
    region_ids                  text                                                          null,
    address                     varchar(128)                                                  null comment '详细地址',
    is_region_like              tinyint(1) unsigned                 default 1                 null comment '针对地区',
    platform_goods_ids          text                                                          null comment '平台商品ids,仅显示',
    platform_sku_ids            text                                                          null,
    is_note                     tinyint(1)                                                    null comment '有无留言备注',
    note                        varchar(128)                                                  null comment '买家卖家包含备注标记',
    receive_name                varchar(64)                                                   null comment '收件人姓名',
    is_like                     tinyint(1) unsigned                 default 1                 null comment '只针对商品和SKU',
    sort                        tinyint(1) unsigned                                           not null,
    color                       varchar(32)                                                   null,
    min_goods_number            mediumint unsigned                                            null comment '最小商品数量',
    max_goods_number            mediumint unsigned                                            null comment '最大商品数量',
    order_amount_type           enum ('goods_amount', 'pay_amount') default 'goods_amount'    null comment '商品总价goods_amount和实付金额pay_amount',
    min_order_amount            decimal(10, 2) unsigned                                       null comment '最小订单金额',
    max_order_amount            decimal(10, 2) unsigned                                       null comment '最大订单金额',
    min_weight                  mediumint unsigned                                            null comment '最小重量',
    max_weight                  mediumint unsigned                                            null comment '最大重量',
    not_reduce_inventory        tinyint(1)                          default 0                 not null,
    is_delete                   tinyint(1)                          default 0                 null comment '是否删除',
    created_time                datetime                            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time           timestamp                           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '订单标记' charset = utf8;

create index facility_id
    on facility_shipment_flag (facility_id);

create table facility_shipment_tactics
(
    facility_shipment_tactics_id   int unsigned auto_increment
        primary key,
    facility_shipment_tactics_name varchar(64)                                   not null,
    facility_id                    int(10)                                       not null,
    warehouse_id                   int(10)                                       null,
    shop_ids                       varchar(512)                                  null,
    region_ids                     text                                          null,
    platform_sku_ids               text                                          null,
    is_like                        tinyint(1) unsigned default 1                 null comment '只针对商品和SKU',
    sort                           tinyint(1) unsigned                           not null,
    created_time                   datetime            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time              timestamp           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint sort
        unique (sort, facility_id)
)
    comment '发货策略表' charset = utf8;

create index facility_id
    on facility_shipment_tactics (facility_id);

create index warehouse_id
    on facility_shipment_tactics (warehouse_id);

create table facility_shipping
(
    facility_shipping_id       int unsigned auto_increment
        primary key,
    facility_id                int(10)                                not null,
    warehouse_id               int(10)                                null,
    shipping_id                int(10)                                not null,
    is_cainiao_thermal         tinyint(1)   default 0                 null,
    is_pdd_thermal             tinyint(1)   default 0                 null,
    is_kuaidi_thermal          tinyint(1)   default 0                 null,
    default_thermal_type       varchar(30)                            not null comment 'PDD,CAINIAO,EXPRESS',
    enable                     tinyint(1)   default 1                 not null comment '1-可用，0-不可用',
    send_addr_code             varchar(30)                            null comment '顺丰原寄地',
    sf_account                 varchar(12)                            null,
    facility_shipping_name     varchar(32)                            null comment '快递名称',
    facility_shipping_user     varchar(32)                            null,
    facility_shipping_password varchar(64)                            null,
    facility_shipping_site     varchar(60)                            null comment '申通网点或顺丰模板',
    facility_shipping_account  varchar(32)                            null comment '月结账号(仅顺丰必填)',
    logistic_service_id        int                                    null comment '快递服务',
    pay_method                 varchar(20)                            null comment '付费方式',
    service_type               varchar(30)  default ''                null comment '顺丰服务类型',
    service_type_code          int(10)      default 0                 null comment '顺丰服务类型编码',
    sort                       int          default 1                 not null,
    cainiao_oauth_id           int unsigned                           null,
    cainiao_branch_code        varchar(128) default ''                not null comment '发件网点code',
    cainiao_branch_name        varchar(128) default ''                not null comment '发件网点名称',
    cainiao_branch_address     varchar(512) default ''                not null comment '发货地',
    cainiao_template_id        int unsigned                           null comment '面单模板id',
    pdd_oauth_id               int unsigned                           null,
    pdd_branch_code            varchar(128) default ''                not null comment '发件网点code',
    pdd_branch_name            varchar(128) default ''                not null comment '发件网点名称',
    pdd_branch_address         varchar(512) default ''                not null comment '发货地',
    pdd_template_id            int unsigned                           null comment '面单模板id',
    kuaidi_template_id         int unsigned                           null comment '面单模板id',
    shipping_fee_template_id   int unsigned                           null comment '运费模板id',
    created_time               datetime     default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time          timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    logistic_service           varchar(512)                           null comment '拼多多增值服务',
    constraint unique_fws_id
        unique (facility_id, warehouse_id, shipping_id)
)
    charset = utf8;

create index cainiao_oauth_id
    on facility_shipping (cainiao_oauth_id);

create index cainiao_template_id
    on facility_shipping (cainiao_template_id);

create index enable
    on facility_shipping (enable);

create index facility
    on facility_shipping (facility_id);

create index kuaidi_template_id
    on facility_shipping (kuaidi_template_id);

create index pdd_oauth_id
    on facility_shipping (pdd_oauth_id);

create index pdd_template_id
    on facility_shipping (pdd_template_id);

create index shipping_id
    on facility_shipping (shipping_id);

create index warehouse_id
    on facility_shipping (warehouse_id);

create table facility_shipping_back_20200422
(
    facility_shipping_id       int unsigned auto_increment
        primary key,
    facility_id                int(10)                                not null,
    warehouse_id               int(10)                                null,
    shipping_id                int(10)                                not null,
    is_cainiao_thermal         tinyint(1)   default 0                 null,
    is_pdd_thermal             tinyint(1)   default 0                 null,
    is_kuaidi_thermal          tinyint(1)   default 0                 null,
    default_thermal_type       varchar(30)                            not null comment 'PDD,CAINIAO,EXPRESS',
    enable                     tinyint(1)   default 1                 not null comment '1-可用，0-不可用',
    send_addr_code             varchar(30)                            null comment '顺丰原寄地',
    sf_account                 varchar(12)                            null,
    facility_shipping_name     varchar(32)                            null comment '快递名称',
    facility_shipping_user     varchar(32)                            null,
    facility_shipping_password varchar(64)                            null,
    facility_shipping_site     varchar(60)                            null comment '申通网点或顺丰模板',
    facility_shipping_account  varchar(32)                            null comment '月结账号(仅顺丰必填)',
    logistic_service_id        int                                    null comment '快递服务',
    pay_method                 varchar(20)                            null comment '付费方式',
    service_type               varchar(30)  default ''                null comment '顺丰服务类型',
    service_type_code          int(10)      default 0                 null comment '顺丰服务类型编码',
    sort                       int          default 1                 not null,
    cainiao_oauth_id           int unsigned                           null,
    cainiao_branch_code        varchar(128) default ''                not null comment '发件网点code',
    cainiao_branch_name        varchar(128) default ''                not null comment '发件网点名称',
    cainiao_branch_address     varchar(512) default ''                not null comment '发货地',
    cainiao_template_id        int unsigned                           null comment '面单模板id',
    pdd_oauth_id               int unsigned                           null,
    pdd_branch_code            varchar(128) default ''                not null comment '发件网点code',
    pdd_branch_name            varchar(128) default ''                not null comment '发件网点名称',
    pdd_branch_address         varchar(512) default ''                not null comment '发货地',
    pdd_template_id            int unsigned                           null comment '面单模板id',
    kuaidi_template_id         int unsigned                           null comment '面单模板id',
    shipping_fee_template_id   int unsigned                           null comment '运费模板id',
    created_time               datetime     default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time          timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    logistic_service           varchar(512)                           null comment '拼多多增值服务',
    constraint unique_fws_id
        unique (facility_id, warehouse_id, shipping_id)
)
    charset = utf8;

create index cainiao_oauth_id
    on facility_shipping_back_20200422 (cainiao_oauth_id);

create index cainiao_template_id
    on facility_shipping_back_20200422 (cainiao_template_id);

create index enable
    on facility_shipping_back_20200422 (enable);

create index facility
    on facility_shipping_back_20200422 (facility_id);

create index kuaidi_template_id
    on facility_shipping_back_20200422 (kuaidi_template_id);

create index pdd_oauth_id
    on facility_shipping_back_20200422 (pdd_oauth_id);

create index pdd_template_id
    on facility_shipping_back_20200422 (pdd_template_id);

create index shipping_id
    on facility_shipping_back_20200422 (shipping_id);

create index warehouse_id
    on facility_shipping_back_20200422 (warehouse_id);

create table facility_shipping_fee_template_mapping
(
    id                       int unsigned auto_increment comment 'id'
        primary key,
    facility_id              int unsigned                        not null,
    warehouse_id             int(10)                             not null,
    shipping_fee_template_id int unsigned                        not null,
    shipping_id              int unsigned                        not null,
    created_time             datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time        timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_shipping_id
        unique (shipping_id, facility_id, warehouse_id)
)
    charset = utf8;

create index created_time
    on facility_shipping_fee_template_mapping (created_time);

create index last_updated_time
    on facility_shipping_fee_template_mapping (last_updated_time);

create index shipping_fee_template_id
    on facility_shipping_fee_template_mapping (shipping_fee_template_id);

create table facility_shipping_template
(
    id                              int unsigned auto_increment
        primary key,
    facility_shipping_template_name varchar(512)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      null,
    facility_id                     int unsigned                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      not null,
    warehouse_id                    int(10)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           null,
    shipping_id                     int unsigned                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      not null,
    facility_address_id             int(10)             default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    thermal_type                    varchar(32)         default 'PDD'                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 null comment ' PDD 拼多多电子面单 CAINIAO 菜鸟电子面单',
    oauth_id                        int unsigned                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      null,
    branch_share_id                 int unsigned        default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '多多分单分享网点id',
    branch_code                     varchar(128)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    not null comment '网点code',
    branch_name                     varchar(128)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    not null comment '网点名称',
    branch_address                  varchar(512)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    not null comment '网点地址',
    shipping_template_id            int unsigned                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      null comment '面单模板id',
    enabled                         tinyint(1)          default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '1 在用，0 已删除',
    sort                            int                 default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    logistic_service                varchar(512)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      null comment '拼多多增值服务',
    custom_locale_start_point       varchar(10)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       null,
    custom_locale_fontsize          double(10, 0)       default 14                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    null,
    custom_locale_text              varchar(256)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    null,
    custom_locale_start_point2      varchar(10)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       null,
    custom_locale_fontsize2         double(10, 0)       default 14                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    null,
    custom_locale_text2             varchar(256)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    null,
    is_show_append_mail             tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_merge_order             tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_goods_name              tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_outer_goods_id          tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_outer_id                tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_goods_alias             tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_style_value             tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_sku_name                tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '规格名称，不同于is_show_style_value，is_show_style_value是有简称显示简称，没有简称显示名称，如果两个字段一样就只取一个',
    is_show_goods_number            tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_buyer_note              tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_seller_note             tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_order_sn                tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_goods_amount            tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_pay_amount              tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '是否展示实付金额',
    is_show_weight                  tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '是否展示订单重量',
    is_split_group_goods            tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '组合商品是否拆分显示',
    is_hide_gift_goods              tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_confirm_time            tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_shop_name               tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_qrcode                  tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_multi_goods             tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '0 不显示，1显示',
    is_show_location                tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_top_logo                tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '0 不显示，1显示',
    is_show_bottom_logo             tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_sum_number              tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    custom_locale_list              varchar(4096)       default '[{"name":"goodsInfo","text":"","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"orderSn","text":"12312-123123","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"shopName","text":"\\u6211\\u662f\\u5e97\\u94fa\\u540d","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"goodsAmount","text":"29.9\\u5143","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"buyerNote","text":"\\u4e70\\u5bb6\\u7559\\u8a00","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"sellerNote","text":"\\u5907\\u6ce8\\uff1a\\u5185\\u90e8\\u5546\\u54c1\\u8bf7\\u5c0f\\u5fc3\\u8f7b\\u653e","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"confirmTime","text":"2020-02-15 12:12:12","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"weight","text":"1kg","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"payAmount","text":"19.9\\u5143","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"sumNumber","text":"2\\u4ef6","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"appendMail","text":"\\u3010\\u8ffd\\u3011","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"mergeOrder","text":"\\u3010\\u5408\\u3011\\u5355\\u6570\\uff1a4\\u5355","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false},{"name":"batchSn","text":"1234567","top":0.1,"left":0.1,"width":50,"height":5,"is_show":false}]' null,
    is_need_custom_locale           tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    is_show_batch_sn                tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    new_model_merge_order_setting   tinyint(1) unsigned default 1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null,
    same_sku_merge_print            tinyint(1) unsigned default 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null comment '相同sku是否合并打印',
    mark                            varchar(256)        default ''                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    not null comment '自定义内容',
    front_size                      int(10)             default 12                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    null comment '字体大小',
    hor_offset                      decimal(10, 1)      default 0.0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   null comment '水平偏移',
    ver_offset                      decimal(10, 1)      default 0.0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   null comment '垂直偏移',
    created_time                    datetime            default CURRENT_TIMESTAMP                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     null comment '创建时间',
    last_updated_time               timestamp           default CURRENT_TIMESTAMP                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on facility_shipping_template (created_time);

create index enabled
    on facility_shipping_template (enabled);

create index facility_address_id
    on facility_shipping_template (facility_address_id);

create index facility_id
    on facility_shipping_template (facility_id);

create index facility_shipping_template_name
    on facility_shipping_template (facility_shipping_template_name);

create index last_updated_time
    on facility_shipping_template (last_updated_time);

create index oauth_id
    on facility_shipping_template (oauth_id);

create index shipping_id
    on facility_shipping_template (shipping_id);

create index sort
    on facility_shipping_template (sort);

create index warehouse_id
    on facility_shipping_template (warehouse_id);

create table finance_ad_fee_setting
(
    finance_ad_fee_setting_id bigint unsigned auto_increment
        primary key,
    shop_id                   mediumint(8)                          not null,
    facility_id               int(10)                               not null,
    platform_goods_id         bigint unsigned                       null,
    count_date                date                                  not null comment '日期',
    ad_fee                    decimal(10, 4)                        not null comment '直通车等推广费用',
    created_time              datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time         timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    type                      varchar(30) default 'search'          null comment 'search 搜索  scene 场景  live 直播'
)
    comment '财务直通车费用' charset = utf8;

create index count_date
    on finance_ad_fee_setting (count_date);

create index created_time
    on finance_ad_fee_setting (created_time);

create index facility_id
    on finance_ad_fee_setting (facility_id);

create index last_updated_time
    on finance_ad_fee_setting (last_updated_time);

create index platform_goods_id
    on finance_ad_fee_setting (platform_goods_id);

create index shop_id
    on finance_ad_fee_setting (shop_id);

create table finance_bill_goods
(
    finance_bill_goods_id               bigint auto_increment
        primary key,
    facility_id                         bigint                                   not null comment 'facility_id',
    shop_id                             bigint                                   not null,
    create_date                         date                                     not null comment '创建日期',
    goods_id                            bigint                                   null,
    sku_id                              bigint                                   null,
    pay_goods_count                     bigint(10)                               not null comment '商品销量',
    pay_amount                          decimal(10, 2)                           not null comment '付款金额',
    pay_platform_discount               decimal(10, 2)                           not null comment '下单平台优惠券',
    pay_shipping_fee                    decimal(10, 2)                           not null comment '下单订单快递费',
    pay_package_fee                     decimal(10, 2)                           not null comment '下单订单包材费',
    pay_goods_purchase_price            decimal(10, 2)                           not null comment '下单订单商品成本价',
    shipped_pay_goods_count             bigint(10)     default 0                 null comment '商品销量（按照发货时间计算）',
    shipped_pay_amount                  decimal(10, 2) default 0.00              null comment '付款金额（按照发货时间计算）',
    shipped_pay_platform_discount       decimal(10, 2) default 0.00              null comment '下单平台优惠券（按照发货时间计算）',
    shipped_pay_shipping_fee            decimal(10, 2) default 0.00              null comment '下单订单快递费（按照发货时间计算）',
    shipped_pay_package_fee             decimal(10, 2) default 0.00              null comment '下单订单包材费（按照发货时间计算）',
    shipped_pay_goods_purchase_price    decimal(10, 2) default 0.00              null comment '下单订单商品成本价（按照发货时间计算）',
    refund_goods_count                  bigint(10)                               not null comment '退款商品数',
    refund_pay_amount                   decimal(10, 2)                           not null comment '退款金额',
    refund_platform_discount            decimal(10, 2)                           not null comment '退款订单平台优惠券',
    refund_shipped_goods_count          int(10)        default 0                 null comment '退款已发货商品数',
    refund_shipped_pay_amount           decimal(10, 2) default 0.00              null comment '退款已发货金额',
    refund_shipped_platform_discount    decimal(10, 2) default 0.00              null comment '退款已发货平台优惠券',
    refund_shipped_package_fee          decimal(10, 2)                           not null comment '退款已发货订单包材费',
    refund_shipped_goods_purchase_price decimal(10, 2)                           not null comment '退款已发货订单商品成本价',
    refund_shipped_shipping_fee         decimal(10, 2)                           not null comment '退款已发货订单快递费',
    refund_un_ship_package_fee          decimal(10, 2)                           not null comment '退款未发货订单包材费',
    refund_un_ship_goods_purchase_price decimal(10, 2)                           not null comment '退款未发货订单商品成本价',
    refund_un_ship_shipping_fee         decimal(10, 2)                           not null comment '退款未发货订单快递费',
    created_time                        datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                   datetime       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '商品财务报表' charset = utf8;

create index create_date
    on finance_bill_goods (create_date);

create index created_time
    on finance_bill_goods (created_time);

create index facility_id
    on finance_bill_goods (facility_id);

create index goods_id
    on finance_bill_goods (goods_id);

create index shop_id
    on finance_bill_goods (shop_id);

create index sku_id
    on finance_bill_goods (sku_id);

create table finance_bill_order
(
    finance_bill_order_id               bigint auto_increment
        primary key,
    facility_id                         bigint                                   not null comment 'facility_id',
    shop_id                             bigint         default 0                 not null,
    create_date                         datetime                                 not null comment '日期',
    shipped_tracking_number_count       bigint(10)                               not null comment '已发货快递单数',
    shipped_order_count                 bigint(10)                               not null comment '已发货订单数',
    pay_order_count                     bigint(10)                               not null comment '支付订单数',
    pay_goods_count                     bigint(10)                               not null comment '商品销量',
    pay_amount                          decimal(10, 2)                           not null comment '付款金额',
    pay_platform_discount               decimal(10, 2)                           not null comment '下单平台优惠券',
    pay_shipping_fee                    decimal(10, 2)                           not null comment '下单订单快递费',
    pay_package_fee                     decimal(10, 2)                           not null comment '下单订单包材费',
    pay_goods_purchase_price            decimal(10, 2)                           not null comment '下单订单商品成本价',
    shipped_pay_order_count             bigint(10)     default 0                 null comment '支付订单数 （按照发货时间计算）',
    shipped_pay_goods_count             bigint(10)     default 0                 null comment '商品销量（按照发货时间计算）',
    shipped_pay_amount                  decimal(10, 2) default 0.00              null comment '付款金额（按照发货时间计算）',
    shipped_pay_platform_discount       decimal(10, 2) default 0.00              null comment '下单平台优惠券（按照发货时间计算）',
    shipped_pay_shipping_fee            decimal(10, 2) default 0.00              null comment '下单订单快递费（按照发货时间计算）',
    shipped_pay_package_fee             decimal(10, 2) default 0.00              null comment '下单订单包材费（按照发货时间计算）',
    shipped_pay_goods_purchase_price    decimal(10, 2) default 0.00              null comment '下单订单商品成本价（按照发货时间计算）',
    refund_order_count                  bigint(10)                               not null comment '退款订单数',
    refund_goods_count                  bigint(10)                               not null comment '退款商品数',
    refund_pay_amount                   decimal(10, 2)                           not null comment '退款金额',
    refund_platform_discount            decimal(10, 2)                           not null comment '退款订单平台优惠券',
    refund_shipped_order_count          int(10)        default 0                 null comment '退款已发货订单数',
    refund_shipped_goods_count          int(10)        default 0                 null comment '退款已发货商品数',
    refund_shipped_pay_amount           decimal(10, 2) default 0.00              null comment '退款已发货金额',
    refund_shipped_platform_discount    decimal(10, 2) default 0.00              null comment '退款已发货平台优惠券',
    refund_shipped_package_fee          decimal(10, 2)                           not null comment '退款已发货订单包材费',
    refund_shipped_goods_purchase_price decimal(10, 2)                           not null comment '退款已发货订单商品成本价',
    refund_shipped_shipping_fee         decimal(10, 2)                           not null comment '退款已发货订单快递费',
    refund_un_ship_package_fee          decimal(10, 2)                           not null comment '退款未发货订单包材费',
    refund_un_ship_goods_purchase_price decimal(10, 2)                           not null comment '退款未发货订单商品成本价',
    refund_un_ship_shipping_fee         decimal(10, 2)                           not null comment '退款未发货订单快递费',
    created_time                        datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                   datetime       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '订单财务报表' charset = utf8;

create index create_date
    on finance_bill_order (create_date);

create index created_time
    on finance_bill_order (created_time);

create index facility_id
    on finance_bill_order (facility_id);

create index shop_id
    on finance_bill_order (shop_id);

create table finance_detail
(
    finance_detail_id     bigint unsigned auto_increment
        primary key,
    order_goods_id        bigint unsigned                          not null,
    order_sn              varchar(32)                              not null,
    shop_id               mediumint(8)                             not null,
    facility_id           int(10)                                  not null,
    pay_status            char(32)       default 'PS_PAYED'        not null comment '0-PS_UNPAYED,2-PS_PAYED,3-PS_REFUND_APPLY,4-PS_REFUNDING，5-PS_REFUND_SUCC',
    refund_time           datetime                                 null comment '退款订单变化时间',
    refund_apply_time     datetime                                 null,
    shipping_fee          decimal(15, 4) default 0.0000            not null comment '快递费',
    package_fee           decimal(15, 4) default 0.0000            not null comment '包材费',
    purchase_price        decimal(15, 4) default 0.0000            not null comment '成本价',
    small_transfer_amount decimal(10, 4) default 0.0000            not null comment '小额打款金额',
    sync_update_status    varchar(32)    default 'WAIT_UPDATE'     not null comment ' WAIT_UPDATE 待更新 UPDATED 已更新 ',
    created_time          datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time     timestamp      default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint order_goods_id
        unique (order_goods_id)
)
    comment '订单商品财务明细表' charset = utf8;

create index created_time
    on finance_detail (created_time);

create index facility_id
    on finance_detail (facility_id);

create index last_updated_time
    on finance_detail (last_updated_time);

create index order_sn
    on finance_detail (order_sn);

create index pay_status
    on finance_detail (pay_status);

create index refund_time
    on finance_detail (refund_time);

create index shop_id
    on finance_detail (shop_id);

create index sync_update_status
    on finance_detail (sync_update_status);

create table finance_estimate_shipping_fee
(
    finance_estimate_shipping_fee_id bigint auto_increment
        primary key,
    facility_id                      bigint                             not null comment 'facility_id',
    shop_id                          bigint   default 0                 not null,
    create_date                      datetime                           not null comment '日期',
    estimate_pay_order_count         bigint(10)                         not null comment '预估快递费的单数，合并订单算一单',
    estimate_pay_shipping_fee        decimal(10, 2)                     not null comment '预估快递费',
    estimate_shipped_order_count     bigint(10)                         not null comment '预估发货订单快递费的单数，合并订单算一单',
    estimate_shipped_shipping_fee    decimal(10, 2)                     not null comment '预估发货订单快递费',
    created_time                     datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time                datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '预估订单快递费记录表' charset = utf8;

create index create_date
    on finance_estimate_shipping_fee (create_date);

create index created_time
    on finance_estimate_shipping_fee (created_time);

create index facility_id
    on finance_estimate_shipping_fee (facility_id);

create index shop_id
    on finance_estimate_shipping_fee (shop_id);

create table finance_manual_bill
(
    finance_manual_bill_id bigint auto_increment
        primary key,
    facility_id            bigint                                 not null comment 'facility_id',
    shop_id                mediumint(8)                           not null,
    start_date             date                                   not null comment '日期',
    end_date               date                                   not null comment '日期',
    bill_type              varchar(128) default 'ONCE_SPEND'      null comment 'ONCE_SPEND一次性支出,DAY_SPEND每天固定支出,MONTH_SPEND每月固定支出,ONCE_INCOME收入',
    amount                 decimal(10, 2)                         not null comment '金额',
    label                  varchar(64)                            null comment '标签',
    note                   varchar(512)                           null comment '备注',
    created_time           datetime     default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time      datetime     default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '手工记账表' charset = utf8;

create index bill_type
    on finance_manual_bill (bill_type);

create index created_time
    on finance_manual_bill (created_time);

create index end_date
    on finance_manual_bill (end_date);

create index facility_id
    on finance_manual_bill (facility_id);

create index label
    on finance_manual_bill (label);

create index shop_id
    on finance_manual_bill (shop_id);

create index start_date
    on finance_manual_bill (start_date);

create table finance_setting
(
    finance_setting_id   int unsigned auto_increment
        primary key,
    facility_id          int(10)                                  not null,
    un_ship_cost         tinyint(3)     default 100               null comment '未发货商品成本退回比例%',
    un_ship_package_fee  tinyint(3)     default 100               null comment '未发货商品包材费退回比例%',
    un_ship_shipping_fee tinyint(3)     default 100               null comment '未发货商品快递费退回比例%',
    shiped_cost          tinyint(3)     default 100               null comment '已发货商品成本退回比例%',
    shiped_package_fee   tinyint(3)     default 0                 null comment '已发货商品包材费退回比例%',
    shiped_shipping_fee  tinyint(3)     default 0                 null comment '已发货商品快递费退回比例%',
    package_fee_is_final tinyint(1)     default 0                 null comment '每个订单是否固定包材费',
    package_fee          decimal(10, 2) default 0.00              null comment '订单固定包材费',
    manual_bill_label    varchar(512)   default '房租,工资,水电,线下收入'   null comment '标签',
    created_time         datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time    timestamp      default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_id
        unique (facility_id)
)
    comment '财务设置' charset = utf8;

create table gift_tactics
(
    gift_tactics_id   int unsigned auto_increment
        primary key,
    facility_id       int unsigned                         not null,
    gift_tactics_name varchar(30)                          not null,
    start_time        datetime                             not null,
    end_time          datetime                             not null,
    time_type         varchar(30)                          not null comment 'PAY_TIME/CONFIRM_TIME',
    type              varchar(30)                          not null comment 'GOODS_NUMBER/GOODS_AMOUNT',
    gift_method       varchar(30)                          not null comment '赠送方式：SELF赠送自己/DIY赠送自定义/RANDOM随机',
    status            varchar(30)                          null comment 'COMMON/CLOSED/DELETED',
    merge_after_gift  tinyint(1) default 0                 null,
    created_time      datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time datetime   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '赠品规则' charset = utf8;

create index created_time
    on gift_tactics (created_time);

create index end_time
    on gift_tactics (end_time);

create index facility_id
    on gift_tactics (facility_id);

create index gift_tactics_name
    on gift_tactics (gift_tactics_name);

create index last_updated_time
    on gift_tactics (last_updated_time);

create index start_time
    on gift_tactics (start_time);

create index status
    on gift_tactics (status);

create table gift_tactics_detail
(
    gift_tactics_detail_id int unsigned auto_increment
        primary key,
    gift_tactics_id        int unsigned                       not null,
    facility_id            int unsigned                       not null,
    shop_id                mediumint unsigned                 not null,
    platform_goods_id      bigint unsigned                    not null,
    platform_sku_ids       text                               null,
    gift_goods_id          bigint                             null comment '系统goods_id, 有效期内简称匹配不能改；当gift_method=SELF时为空，其他不能为空送自己，=DIY时全送，=RANDOM时随机选1个',
    gift_sku_ids           varchar(512)                       null comment '系统sku_id, 当gift_method=SELF时为空，其他不能为空送自己，=DIY时全送，=RANDOM时随机选1个',
    buy_count              int(10)                            not null comment '购买件数/购买金额',
    give_count             int(10)                            not null comment '赠送件数',
    is_give_repeat         tinyint(1)                         not null comment '是否累加',
    created_time           datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time      datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '赠品规则详情' charset = utf8;

create index auto_shard_key_facility_id
    on gift_tactics_detail (facility_id);

create index created_time
    on gift_tactics_detail (created_time);

create index gift_tactics_id
    on gift_tactics_detail (gift_tactics_id);

create index last_updated_time
    on gift_tactics_detail (last_updated_time);

create index platform_goods_id
    on gift_tactics_detail (platform_goods_id);

create index shop_id
    on gift_tactics_detail (shop_id);

create table goods
(
    goods_id           bigint unsigned auto_increment
        primary key,
    facility_id        int unsigned                          not null,
    goods_name         varchar(300)                          not null comment '商品名称',
    goods_category     varchar(30)                           null,
    goods_brand        varchar(30)                           null,
    goods_alias        varchar(300)                          null comment '商品简称',
    image_url          varchar(256)                          null comment '商品图片url',
    outer_id           varchar(50)                           not null comment '商家编码，默认goods_id。没有绑定关系前可修改',
    has_sku_spec       tinyint(1)                            null,
    min_purchase_price decimal(10, 2)                        null comment '商品的最低采购价',
    max_purchase_price decimal(10, 2)                        null comment '商品的最高采购价',
    enabled            tinyint(1)  default 1                 null,
    is_onsale          bigint(1)   default 1                 null comment '是否下架',
    is_delete          tinyint(1)  default 0                 null,
    mapping_count      smallint(5) default 0                 null,
    created_type       varchar(30)                           not null comment 'OPENAPI_CREATED 同步自动创建，GOODS_MAPPING_CREATED 商品匹配创建，GOODS_MANAGER_CREATED 商品管理创建',
    created_time       datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time  datetime    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_outer_id
        unique (facility_id, outer_id)
)
    comment '商品表' charset = utf8;

create index created_time
    on goods (created_time);

create index enabled
    on goods (enabled);

create index facility_id
    on goods (facility_id);

create index goods_alias
    on goods (goods_alias);

create index is_delete
    on goods (is_delete);

create index is_onsale
    on goods (is_onsale);

create index last_updated_time
    on goods (last_updated_time);

create index outer_id
    on goods (outer_id);

create table goods_back
(
    goods_id           bigint unsigned auto_increment
        primary key,
    facility_id        int unsigned                         not null,
    goods_name         varchar(300)                         not null comment '商品名称',
    goods_category     varchar(30)                          null,
    goods_brand        varchar(30)                          null,
    goods_alias        varchar(300)                         null comment '商品简称',
    image_url          varchar(256)                         null comment '商品图片url',
    outer_id           varchar(50)                          not null comment '商家编码，默认goods_id。没有绑定关系前可修改',
    has_sku_spec       tinyint(1)                           null,
    min_purchase_price decimal(10, 2)                       null comment '商品的最低采购价',
    max_purchase_price decimal(10, 2)                       null comment '商品的最高采购价',
    enabled            tinyint(1) default 1                 null,
    created_type       varchar(30)                          not null comment 'OPENAPI_CREATED 同步自动创建，GOODS_MAPPING_CREATED 商品匹配创建，GOODS_MANAGER_CREATED 商品管理创建',
    created_time       datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time  datetime   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    back_created_time  datetime   default CURRENT_TIMESTAMP null comment '这才是创建时间，那个created_time是原表的创建时间'
)
    comment '商品表' charset = utf8;

create index created_time
    on goods_back (created_time);

create index enabled
    on goods_back (enabled);

create index facility_id
    on goods_back (facility_id);

create index last_updated_time
    on goods_back (last_updated_time);

create index outer_id
    on goods_back (outer_id);

create table goods_mapping
(
    goods_mapping_id  bigint unsigned auto_increment
        primary key,
    facility_id       int unsigned                       not null,
    shop_id           bigint                             null,
    platform_goods_id bigint                             null,
    goods_id          bigint                             null,
    mapping_type      varchar(30)                        not null comment 'OUTER_ID 根据商家编码匹配，USER 根据用户选择匹配，AUTO_CREATED 自动创建',
    created_time      datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint platform_goods_id
        unique (platform_goods_id, shop_id)
)
    comment '商品匹配表' charset = utf8;

create index created_time
    on goods_mapping (created_time);

create index facility_id
    on goods_mapping (facility_id);

create index goods_id
    on goods_mapping (goods_id);

create index last_updated_time
    on goods_mapping (last_updated_time);

create index mapping_type
    on goods_mapping (mapping_type);

create index shop_id
    on goods_mapping (shop_id);

create table goods_mapping_history
(
    goods_mapping_history_id bigint unsigned auto_increment
        primary key,
    goods_mapping_id         bigint                             null,
    facility_id              int unsigned                       not null,
    shop_id                  bigint                             null,
    platform_goods_id        bigint                             null,
    goods_id                 bigint                             null,
    mapping_type             varchar(30)                        not null comment 'OUTER_ID 根据商家编码匹配，USER 根据用户选择匹配，AUTO_CREATED 自动创建',
    history_created_time     datetime default CURRENT_TIMESTAMP null,
    created_time             datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time        datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '商品匹配历史记录表' charset = utf8;

create index created_time
    on goods_mapping_history (created_time);

create index facility_id
    on goods_mapping_history (facility_id);

create index goods_id
    on goods_mapping_history (goods_id);

create index goods_mapping_id
    on goods_mapping_history (goods_mapping_id);

create index last_updated_time
    on goods_mapping_history (last_updated_time);

create index mapping_type
    on goods_mapping_history (mapping_type);

create index platform_goods_id
    on goods_mapping_history (platform_goods_id);

create index shop_id
    on goods_mapping_history (shop_id);

create table group_sku_mapping
(
    group_sku_mapping_id bigint unsigned auto_increment
        primary key,
    facility_id          int unsigned                       not null,
    platform_sku_id      bigint unsigned                    not null,
    sku_id               bigint unsigned                    not null,
    number               int(10)                            not null,
    package_fee          decimal(10, 2)                     null,
    created_time         datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time    datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '组合商品表' charset = utf8;

create index created_time
    on group_sku_mapping (created_time);

create index facility_id
    on group_sku_mapping (facility_id);

create index last_updated_time
    on group_sku_mapping (last_updated_time);

create index platform_sku_id
    on group_sku_mapping (platform_sku_id);

create index sku_id
    on group_sku_mapping (sku_id);

create table inventory
(
    inventory_id          bigint auto_increment
        primary key,
    facility_id           int(10)                                  not null comment '仓库id',
    warehouse_id          int(10)                                  null,
    goods_id              bigint                                   not null,
    sku_id                bigint                                   not null,
    status                char(30)       default 'OK'              null comment 'OK,CANCEL,DELETE',
    quantity              int                                      not null comment '库存',
    warning_quantity      int            default 0                 null comment '库存警戒值',
    purchase_price        decimal(10, 2) default 0.00              null comment 'SKU采购价',
    purchase_price_is_set tinyint(1)     default 0                 null comment '成本价是否设置',
    location_code         varchar(64)                              null comment '库位',
    created_time          datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time     timestamp      default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint sku_id
        unique (sku_id, warehouse_id)
)
    comment '库存表' charset = utf8;

create index auto_shard_key_facility_id
    on inventory (facility_id);

create index created_time
    on inventory (created_time);

create index goods_id
    on inventory (goods_id);

create index status
    on inventory (status);

create index warehouse_id
    on inventory (warehouse_id);

create definer = erp_test@`%` trigger sku_quantity_insert
    after insert
    on inventory
    for each row
BEGIN
    IF new.quantity <> 0 THEN
        SELECT sum(quantity) AS sku_quantity
        INTO @sku_quantity
        FROM inventory
        WHERE sku_id = new.sku_id;
        UPDATE sku
        SET sku_quantity = @sku_quantity
        WHERE sku_id = new.sku_id;
    END IF;
END;

create definer = erp_test@`%` trigger sku_quantity_update
    after update
    on inventory
    for each row
BEGIN
    IF new.quantity <> old.quantity THEN
        SELECT sum(quantity) AS sku_quantity
        INTO @sku_quantity
        FROM inventory
        WHERE sku_id = new.sku_id;
        UPDATE sku
        SET sku_quantity = @sku_quantity
        WHERE sku_id = old.sku_id;
    END IF;
END;

create table inventory_detail
(
    inventory_detail_id bigint auto_increment
        primary key,
    inventory_id        bigint                             not null,
    facility_id         int(10)                            not null comment '店铺id',
    warehouse_id        int(10)                            null,
    sku_id              bigint                             not null,
    goods_id            bigint                             not null,
    quantity            int                                not null comment '负数出库，正数入库',
    detail_type         char(20)                           not null comment 'PURCHASE_IN(采购入库),VARIANCE(盘点),GT_OUT(供应商退货),SALE_OUT(销售出库) ',
    created_date        date                               null comment '创建日期',
    created_time        datetime default CURRENT_TIMESTAMP null comment '创建时间',
    created_user        varchar(60)                        null,
    constraint sku_id_detail_type_created_date
        unique (sku_id, warehouse_id, detail_type, created_date)
)
    comment '库存变化表' charset = utf8;

create index created_date
    on inventory_detail (created_date);

create index created_time
    on inventory_detail (created_time);

create index detail_type
    on inventory_detail (detail_type);

create index facility_id
    on inventory_detail (facility_id);

create index goods_id
    on inventory_detail (goods_id);

create index inventory_id
    on inventory_detail (inventory_id);

create index sku_id
    on inventory_detail (sku_id);

create index warehouse_id
    on inventory_detail (warehouse_id);

create table inventory_detail_order
(
    inventory_detail_order_id bigint auto_increment
        primary key,
    inventory_detail_id       bigint                             not null,
    inventory_id              bigint                             not null,
    facility_id               int(10)                            not null,
    warehouse_id              int(10)                            null,
    sku_id                    bigint                             not null,
    goods_id                  bigint                             not null,
    quantity                  int                                not null,
    shipment_id               bigint unsigned                    not null,
    order_sn                  varchar(32)                        not null,
    created_time              datetime default CURRENT_TIMESTAMP null comment '创建时间'
)
    comment '库存订单表' charset = utf8;

create index created_time
    on inventory_detail_order (created_time);

create index facility_id
    on inventory_detail_order (facility_id);

create index goods_id
    on inventory_detail_order (goods_id);

create index inventory_detail_id
    on inventory_detail_order (inventory_detail_id);

create index inventory_id
    on inventory_detail_order (inventory_id);

create index shipment_id
    on inventory_detail_order (shipment_id);

create index sku_id
    on inventory_detail_order (sku_id);

create index warehouse_id
    on inventory_detail_order (warehouse_id);

create table inventory_import_history
(
    inventory_import_history_id bigint unsigned auto_increment
        primary key,
    facility_id                 int unsigned                        null,
    inventory_id                bigint                              not null,
    before_quantity             int(10)                             null comment '导入前库存',
    after_quantity              int(10)                             null comment '导入后库存',
    variance_quantity           int(10)                             null comment '盘盈亏数量',
    origin_file_name            varchar(128)                        not null,
    created_time                datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time           timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on inventory_import_history (created_time);

create index facility_id
    on inventory_import_history (facility_id);

create index inventory_id
    on inventory_import_history (inventory_id);

create index last_updated_time
    on inventory_import_history (last_updated_time);

create table inventory_location_setting
(
    id          bigint unsigned auto_increment
        primary key,
    facility_id int unsigned not null,
    zone_code   varchar(16)  null comment '库区编码',
    length      int          not null comment '库区长',
    width       int          not null comment '库区宽',
    constraint facility_id_zone_code
        unique (facility_id, zone_code)
)
    comment '库位设置表  A-1-1' charset = utf8;

create table mailnos
(
    id                            bigint unsigned auto_increment
        primary key,
    tracking_number               varchar(50)                            not null,
    shop_id                       mediumint(8)                           null,
    status                        varchar(16)  default 'INIT'            not null comment 'INIT：申请，USED：已经绑定shipment，FINNAL：已出库，IGNORE:忽略',
    print_type                    varchar(16)  default 'single'          null comment '打印类型 multi 追加面单 single 正常打印',
    shipping_id                   int(10)      default 0                 not null,
    station                       varchar(30)  default ''                null comment '站点名称',
    station_no                    varchar(16)  default '站点编号'            null,
    sender_branch_no              varchar(16)                            null comment '韵达 始发地编码',
    sender_branch                 varchar(30)                            null comment '韵达 始发地',
    package_no                    varchar(16)                            null comment '韵达 集包编码',
    package_name                  varchar(30)                            null comment ' 韵达 集包',
    lattice_mouth_no              varchar(50)                            null comment '韵达专用',
    express_type                  varchar(3)                             null comment '顺丰 服务类型',
    pay_method                    varchar(3)                             null comment '顺丰 支付方式',
    origin_code                   varchar(20)                            null comment '原寄地区域代码',
    dest_code                     varchar(20)                            null comment '目的地区域代码',
    package_id                    bigint unsigned                        not null,
    created_time                  datetime     default CURRENT_TIMESTAMP null,
    last_update_time              timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    thermal_type                  varchar(30)  default 'THERMAL'         not null,
    facility_id                   int(10)                                not null,
    warehouse_id                  int(10)                                null,
    branch_name                   varchar(128)                           null,
    oauth_id                      int unsigned                           null,
    branch_share_id               int unsigned default 0                 not null comment '多多分单分享网点id',
    oauth_share_id                int(10)      default 0                 null,
    facility_shipping_template_id int(10)                                null,
    encrypted_data                text                                   null comment '获取打印内容的密文',
    signature                     varchar(50)                            null comment '密文签名',
    constraint tracking_number_shipping_id
        unique (tracking_number, shipping_id),
    constraint u_shipment_id
        unique (package_id, shipping_id, thermal_type)
)
    comment '热敏面单号' charset = utf8;

create index IDX_SHIPPING_ID_FACILITY_ID_STATUS
    on mailnos (shipping_id, facility_id, status);

create index facility_id
    on mailnos (facility_id);

create index facility_shipping_template_id
    on mailnos (facility_shipping_template_id);

create index idx_created_time
    on mailnos (created_time);

create index last_update_time
    on mailnos (last_update_time);

create index oauth_share_id
    on mailnos (oauth_share_id);

create index print_type
    on mailnos (print_type);

create index shipping_id
    on mailnos (shipping_id);

create index shop_id
    on mailnos (shop_id);

create index station_no
    on mailnos (station_no);

create index status
    on mailnos (status);

create index thermal_type
    on mailnos (thermal_type);

create index tracking_number
    on mailnos (tracking_number);

create index warehouse_id
    on mailnos (warehouse_id);

create table mailnos_extension
(
    id             bigint unsigned not null comment 'mailnos 的id'
        primary key,
    facility_id    int(10)         not null,
    signature      varchar(512)    null comment '密文签名',
    encrypted_data text            null comment '获取打印内容的密文'
)
    comment '单号扩展表' charset = utf8;

create index facility_id
    on mailnos_extension (facility_id);

create table manu_order
(
    manu_order_id       bigint unsigned auto_increment
        primary key,
    shipping_id         int unsigned         default 0                 null,
    shipping_name       varchar(120)         default '未选择快递'           null,
    tracking_number     varchar(50)                                    null,
    province_id         smallint(5)                                    not null comment '收件人所在省id',
    province_name       varchar(64)                                    not null comment '收件人所在省，如浙江省、北京',
    city_id             smallint(5)                                    not null comment '收件人所在市id',
    city_name           varchar(64)                                    not null comment '收件人所在市，如杭州市、上海市',
    district_id         smallint(5)                                    not null comment '收件人所在县id',
    district_name       varchar(64)                                    not null comment '收件人所在县（区）',
    shipping_address    varchar(256)                                   not null comment '收件人详细地址，不包含省市',
    receive_name        varchar(64)                                    not null comment '收件人姓名',
    mobile              varchar(16)                                    not null comment '收件人移动电话',
    facility_id         int unsigned                                   not null comment '发货地（仓）',
    remark              varchar(512)         default ''                not null comment '自定义内容',
    is_print_tracking   tinyint(1)           default 0                 null comment '面单是否打印',
    created_user        varchar(64)                                    not null comment '收件人姓名',
    created_time        datetime             default CURRENT_TIMESTAMP null comment '创建时间',
    print_time          datetime                                       null comment '打印时间',
    last_updated_time   timestamp            default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    original_package_id bigint                                         null,
    platform_order_sn   varchar(32)          default ''                null,
    type                enum ('ADD', 'MANU') default 'MANU'            null comment 'ADD 追加面单,MANU 手工订单',
    batch_sn            varchar(20)          default ''                null,
    batch_index         int                  default 1                 null comment '批次内的排序',
    batch_type          varchar(20)          default 'REF'             null comment 'REF:关联创建，FREE：自由创建，ADD：追加创建，EXCEL：批量导入',
    thermal_type        varchar(30)                                    null comment '电子面单的类型'
)
    comment '人工录单表' charset = utf8;

create index batch_type
    on manu_order (batch_type);

create index city_id
    on manu_order (city_id);

create index created_time
    on manu_order (created_time);

create index district_id
    on manu_order (district_id);

create index facility_id
    on manu_order (facility_id);

create index facility_id_batch_sn_index
    on manu_order (facility_id, batch_sn, batch_index);

create index is_print_tracking
    on manu_order (is_print_tracking);

create index last_updated_time
    on manu_order (last_updated_time);

create index mobile
    on manu_order (mobile);

create index print_time
    on manu_order (print_time);

create index province_id
    on manu_order (province_id);

create index receive_name
    on manu_order (receive_name);

create index shipping_id
    on manu_order (shipping_id);

create index tracking_number
    on manu_order (tracking_number);

create table manu_order_package
(
    id                bigint unsigned auto_increment
        primary key,
    package_id        bigint unsigned                       not null,
    manu_order_id     bigint unsigned                       not null,
    status            varchar(30) default 'OK'              null comment 'OK 状态，cancel 发货单取消，合并取消原发货单',
    facility_id       int(10)                               not null,
    created_time      datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint package_id_2
        unique (package_id, manu_order_id)
)
    comment '手工订单发货包裹表' charset = utf8;

create index created_time
    on manu_order_package (created_time);

create index facility_id
    on manu_order_package (facility_id);

create index last_updated_time
    on manu_order_package (last_updated_time);

create index manu_order_id
    on manu_order_package (manu_order_id);

create index manu_order_status
    on manu_order_package (manu_order_id, status);

create index package_id
    on manu_order_package (package_id);

create index status
    on manu_order_package (status);

create table manu_task
(
    task_id      bigint unsigned auto_increment
        primary key,
    facility_id  int(10)                               not null,
    batch_sn     varchar(20) default ''                null,
    batch_type   varchar(20) default 'REF'             null comment 'REF:关联创建，FREE：自由创建，ADD：追加创建，EXCEL：批量导入',
    created_time datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    constraint uidx_shop_id_batch_sn
        unique (batch_sn)
)
    comment '人工录单批次表' charset = utf8;

create index batch_type
    on manu_task (batch_type);

create index created_time
    on manu_task (created_time);

create index facility_id
    on manu_task (facility_id);

create table multi_goods_shipment
(
    shipment_id                   bigint unsigned auto_increment
        primary key,
    order_sn                      varchar(32)                                                                                                                                   not null,
    platform_order_sn             varchar(32)                                                                                                                                   null,
    created_user                  varchar(30)                                                                                                         default 'OPENAPI'         null,
    platform_name                 varchar(32)                                                                                                         default 'pinduoduo'       not null,
    shop_id                       mediumint(8)                                                                                                                                  not null,
    facility_id                   int unsigned                                                                                                        default 0                 null comment '发货地（仓）',
    warehouse_id                  int(10)                                                                                                                                       null,
    warehouse_name                varchar(64)                                                                                                                                   null,
    is_print_tracking             tinyint(1)                                                                                                          default 0                 null comment '面单是否打印',
    is_print_waybill              tinyint(1)                                                                                                          default 0                 null comment '发货单是否打印',
    sku_number                    mediumint unsigned                                                                                                  default 1                 not null comment '商品种类数量',
    goods_number                  mediumint unsigned                                                                                                  default 1                 not null comment '商品总数量',
    shipment_status               char(32)                                                                                                            default 'WAIT_SHIP'       null comment 'WAIT_SHIP 未发货,SHIPPED 已发货',
    status                        enum ('CONFIRM', 'DELETED', 'STOP', 'CANCEL')                                                                       default 'CONFIRM'         null comment 'CONFIRM 确认,CANCEL 取消,DELETED 被合并作废，STOP 合并订单中有订单取消',
    order_flag                    int(10)                                                                                                             default 0                 null,
    tactics_id                    int(10)                                                                                                                                       null,
    tactics_name                  varchar(64)                                                                                                                                   null,
    order_flag_name               varchar(30)                                                                                                                                   null,
    address_id                    bigint unsigned                                                                                                     default 0                 null,
    province_id                   smallint(5)                                                                                                                                   not null comment '收件人所在省id',
    province_name                 varchar(64)                                                                                                                                   not null comment '收件人所在省，如浙江省、北京',
    city_id                       smallint(5)                                                                                                                                   not null comment '收件人所在市id',
    city_name                     varchar(64)                                                                                                                                   not null comment '收件人所在市，如杭州市、上海市',
    district_id                   smallint(5)                                                                                                                                   not null comment '收件人所在县id',
    district_name                 varchar(64)                                                                                                                                   not null comment '收件人所在县（区）',
    town_name                     varchar(32)                                                                                                                                   null comment '收件人所在镇（乡）',
    shipping_address              varchar(256)                                                                                                                                  not null comment '收件人详细地址，不包含省市',
    receive_name                  varchar(64)                                                                                                                                   not null comment '收件人姓名',
    mobile                        varchar(16)                                                                                                                                   not null comment '收件人移动电话',
    shipping_time                 datetime                                                                                                                                      null comment '发货时间',
    order_created_time            datetime                                                                                                                                      null comment '下单时间（yyyy-MM-dd HH:mm:ss）',
    confirm_time                  datetime                                                                                                                                      null comment '确认时间（yyyy-MM-dd HH:mm:ss）',
    shipping_due_time             datetime                                                                                                                                      null comment '应发时间',
    print_time                    datetime                                                                                                                                      null comment '打印时间',
    shipping_user                 varchar(128)                                                                                                                                  null comment '发件人，其他平台发的，openapi',
    goods_amount                  decimal(10, 2)                                                                                                      default 0.00              null comment '商品总金额',
    shipping_amount               decimal(10, 2)                                                                                                      default 0.00              null comment '运费',
    order_amount                  decimal(10, 2)                                                                                                      default 0.00              null comment '应付金额 = goods_amount + shipping_amount',
    discount_amount               decimal(10, 2)                                                                                                      default 0.00              null comment '折扣费',
    pay_amount                    decimal(10, 2)                                                                                                      default 0.00              null comment '实际支付费 = order_amount - discount_amount',
    is_note                       tinyint(1)                                                                                                          default 0                 null comment '是否有备注',
    order_type                    tinyint(3)                                                                                                          default 1                 null comment '1 普通订单，2 跨境，3 试用。。这是以前的，现在用来当做发货方式：1、普通发货，2、扫描发货，3、导入发货，后面可以考虑平台发货4',
    buyer_note                    varchar(512)                                                                                                                                  null comment '买家备注',
    seller_note                   varchar(512)                                                                                                                                  null comment '卖家备注',
    buyer_id                      varchar(32)                                                                                                                                   not null comment '买家ID，当 buyer_type 为 1 时，buyer_id 的值等于 weixin_user_id 的值',
    buyer_nick                    varchar(32)                                                                                                                                   not null comment '买家昵称',
    seller_flag                   int                                                                                                                                           null comment '卖家备注旗帜 红、黄、绿、蓝、紫 分别对应 1、2、3、4、5',
    shipping_type                 varchar(32)                                                                                                                                   not null comment '创建交易时的物流方式。可选值：express（快递），fetch（到店自提）',
    shipping_id                   int unsigned                                                                                                        default 0                 null,
    facility_shipping_template_id int(10)                                                                                                                                       null,
    shipping_name                 varchar(120)                                                                                                        default '未选择快递'           null,
    tracking_number               varchar(50)                                                                                                                                   null,
    sortation_name                varchar(30)                                                                                                         default ''                null comment '大头笔信息',
    sortation_code                varchar(16)                                                                                                         default ''                null comment '大头笔信息',
    origin_code                   varchar(16)                                                                                                                                   null comment '发件网点信息',
    origin_name                   varchar(30)                                                                                                                                   null comment '发件网点信息',
    consolidation_code            varchar(16)                                                                                                                                   null comment '集包地编码',
    consolidation_name            varchar(30)                                                                                                                                   null comment ' 集包地',
    route_code                    varchar(50)                                                                                                                                   null comment '三段码信息',
    created_time                  datetime                                                                                                            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time             timestamp                                                                                                           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    sku_number_type               enum ('SINGLE', 'SINGLE_SKU', 'MULTI_SKU')                                                                          default 'SINGLE'          null comment 'SINGLE 单款单件,SINGLE_SKU 单款多件 ,MULTI_SKU 多款多件',
    is_buyer_note                 tinyint(1)                                                                                                          default 0                 null comment '是否有买家备注',
    is_seller_note                tinyint(1)                                                                                                          default 0                 null comment '是否有卖家备注',
    thermal_type                  enum ('INIT', 'CAINIAO', 'PDD', 'EXPRESS')                                                                          default 'INIT'            not null,
    is_cod                        tinyint(1)                                                                                                          default 0                 null comment '是否货到付款',
    group_status                  tinyint(1)                                                                                                          default 1                 not null comment '成团状态：0：拼团中、1：已成团、2：团失败',
    is_merged                     tinyint(1)                                                                                                          default 0                 not null comment '0 不合并，1 合并订单',
    is_main_shipment              tinyint(1)                                                                                                          default 1                 not null comment '1 主订单，0 合并订单中的被合并订单',
    merged_main_shipment_id       bigint unsigned                                                                                                     default 0                 null comment '0 不合并，合并订单的主订单号',
    buyer_rate                    tinyint(1)                                                                                                          default 0                 null comment '买家评价 0 未评价 1 已评价',
    seller_rate                   tinyint(1)                                                                                                          default 0                 null comment '卖家评价 0 未评价 1 已评价',
    pre_shipping_time             datetime                                                                                                                                      null comment '预发货时间',
    accept_time                   datetime                                                                                                                                      null,
    logistic_status               enum ('ACCEPT_REPEAT', 'SIGNED', 'DELIVERING', 'STATION', 'ACCEPT', 'QUESTION', 'WAIT_ACCEPT', 'INIT', 'COMPLETED') default 'WAIT_ACCEPT'     not null comment 'ACCEPT_REPEAT 重复揽件,SIGNED 签收,DELIVERING派件,STATION 物流,ACCEPT 揽件,WAIT_ACCEPT 待揽件,INIT 初始化,COMPLETED 完结',
    last_route_context            text                                                                                                                                          null comment '最新的路由走件信息',
    is_pdd_cancel_send            tinyint(1)                                                                                                          default 0                 null comment '是否平台取消发货',
    weight                        decimal(11, 3)                                                                                                                                null,
    is_promise_delivery           tinyint(1)                                                                                                          default 0                 null comment '是否承诺发货，有承诺发货时间就为1，如果只有承诺快递的话还是0',
    promise_delivery_time         datetime                                                                                                                                      null comment '（优先发货）承诺发货时间',
    promise_shipping_id           int(10)                                                                                                             default 0                 not null comment '承诺发货快递ID',
    promise_shipping_name         varchar(30)                                                                                                                                   null comment '承诺发货快递名称',
    is_add_price_sf               tinyint(1)                                                                                                          default 0                 null comment '是否顺丰加价',
    extra_delivery_list           varchar(256)                                                                                                                                  null comment '一单多包情况的其他包裹单号，暂时只支持拼多多'
)
    comment '订单表' charset = utf8;

create index address_id
    on multi_goods_shipment (address_id);

create index buyer_rate
    on multi_goods_shipment (buyer_rate);

create index city_id
    on multi_goods_shipment (city_id);

create index confirm_time
    on multi_goods_shipment (confirm_time);

create index created_time
    on multi_goods_shipment (created_time);

create index created_user
    on multi_goods_shipment (created_user);

create index district_id
    on multi_goods_shipment (district_id);

create index facility_id
    on multi_goods_shipment (facility_id);

create index facility_shipment_status
    on multi_goods_shipment (facility_id, shipment_status, status);

create index facility_shipping_template_id
    on multi_goods_shipment (facility_shipping_template_id);

create index group_status
    on multi_goods_shipment (group_status);

create index is_add_price_sf
    on multi_goods_shipment (is_add_price_sf);

create index is_buyer_note
    on multi_goods_shipment (is_buyer_note);

create index is_main_shipment
    on multi_goods_shipment (is_main_shipment);

create index is_merged
    on multi_goods_shipment (is_merged);

create index is_note
    on multi_goods_shipment (is_note);

create index is_print_tracking
    on multi_goods_shipment (is_print_tracking);

create index is_print_waybill
    on multi_goods_shipment (is_print_waybill);

create index is_promise_delivery
    on multi_goods_shipment (is_promise_delivery);

create index is_seller_note
    on multi_goods_shipment (is_seller_note);

create index last_updated_time
    on multi_goods_shipment (last_updated_time);

create index merged_main_shipment_id
    on multi_goods_shipment (merged_main_shipment_id);

create index mobile
    on multi_goods_shipment (mobile);

create index order_flag
    on multi_goods_shipment (order_flag);

create index order_sn
    on multi_goods_shipment (order_sn);

create index order_type
    on multi_goods_shipment (order_type);

create index platform_name
    on multi_goods_shipment (platform_name);

create index platform_order_sn
    on multi_goods_shipment (platform_order_sn);

create index pre_shipping_time
    on multi_goods_shipment (shipment_status, pre_shipping_time);

create index print_time
    on multi_goods_shipment (print_time);

create index promise_shipping_id
    on multi_goods_shipment (promise_shipping_id);

create index province_id
    on multi_goods_shipment (province_id);

create index receive_name
    on multi_goods_shipment (receive_name);

create index seller_rate
    on multi_goods_shipment (seller_rate);

create index shipment_status
    on multi_goods_shipment (shipment_status);

create index shipping_due_time
    on multi_goods_shipment (shipping_due_time);

create index shipping_id
    on multi_goods_shipment (shipping_id);

create index shipping_time
    on multi_goods_shipment (shipping_time);

create index shop_id
    on multi_goods_shipment (shop_id);

create index status
    on multi_goods_shipment (status);

create index tactics_id
    on multi_goods_shipment (tactics_id);

create index tracking_number
    on multi_goods_shipment (tracking_number);

create index warehouse_id
    on multi_goods_shipment (warehouse_id);

create table multi_goods_shipment_extension
(
    shipment_id                  bigint unsigned                           not null
        primary key,
    facility_id                  int unsigned                              not null,
    encrypt_shop_id              int(10)                                   not null comment '加密的shop_id',
    mobile_encrypt               varchar(256)                              null comment '手机号密文原串',
    mobile_search_text           varchar(32)                               null comment '手机号检索串',
    receive_name_encrypt         varchar(512)                              null comment '收件人密文原串',
    receive_name_search_text     varchar(128)                              null comment '收件人检索串',
    shipping_address_encrypt     varchar(1024)                             null comment '详细地址密文原串',
    shipping_address_search_text varchar(1024)                             null comment '详细地址检索串',
    aid                          varchar(512)                              null comment '淘宝的OAID和1688的CAID',
    created_time                 datetime        default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time            timestamp       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    receive_name_mask            varchar(64)                               null comment '收件人姓名',
    mobile_mask                  varchar(16)                               null comment '收件人移动电话',
    shipping_address_mask        varchar(256)                              null comment '收件人详细地址，不包含省市',
    address_id                   bigint unsigned default 0                 null
)
    comment '订单extension表' charset = utf8;

create index address_id
    on multi_goods_shipment_extension (address_id);

create index encrypt_shop_id
    on multi_goods_shipment_extension (encrypt_shop_id);

create index facility_id
    on multi_goods_shipment_extension (facility_id);

create index mobile_mask
    on multi_goods_shipment_extension (mobile_mask);

create index mobile_search_text
    on multi_goods_shipment_extension (mobile_search_text);

create index receive_name_search_text
    on multi_goods_shipment_extension (receive_name_search_text);

create index shipping_address_search_text
    on multi_goods_shipment_extension (shipping_address_search_text);

create table multi_goods_shipment_goods
(
    order_goods_id          bigint unsigned auto_increment
        primary key,
    shipment_id             bigint unsigned                              not null,
    shipment_status         char(32)           default 'WAIT_SHIP'       null comment 'WAIT_SHIP 未发货,SHIPPED 已发货',
    order_sn                varchar(32)                                  null,
    shop_id                 mediumint(8)                                 not null,
    facility_id             int(10)                                      not null,
    order_id                bigint unsigned                              not null comment '订单号',
    platform_sku_id         bigint                                       null,
    sku_id                  bigint                                       null,
    outer_id                varchar(50)                                  null comment '外部编码',
    outer_goods_id          varchar(512)                                 null comment '外部编码-商品级',
    platform_goods_id       bigint                                       null,
    goods_id                bigint                                       null,
    goods_name              varchar(256)                                 not null comment '商品名',
    goods_alias             varchar(300)                                 null comment '商品简称',
    sku_alias               varchar(300)                                 null comment '商品简称',
    style_value             varchar(256)                                 not null comment '样式名-组合',
    goods_number            mediumint unsigned default 1                 not null comment '商品数量',
    image_url               varchar(256)                                 null comment '商品图片url',
    created_time            datetime           default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time       timestamp          default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    price_dot               decimal(10, 2)     default 0.00              not null comment '商品原价',
    discount_amount         decimal(10, 2)     default 0.00              null comment '折扣费',
    goods_amount            decimal(10, 2)     default 0.00              null comment '商品总金额= price_dot-discount_amount',
    original_shipment_id    bigint unsigned                              null,
    original_order_goods_id bigint unsigned                              null,
    platform_order_goods_sn varchar(64)                                  null,
    gift_tactics_id         int(10)            default 0                 null,
    gift_tactics_detail_id  int(10)            default 0                 null
)
    comment '订单商品表' charset = utf8;

create index created_time
    on multi_goods_shipment_goods (created_time);

create index facility_id
    on multi_goods_shipment_goods (facility_id);

create index gift_tactics_id
    on multi_goods_shipment_goods (gift_tactics_id);

create index goods_alias
    on multi_goods_shipment_goods (goods_alias);

create index goods_id
    on multi_goods_shipment_goods (goods_id);

create index goods_name
    on multi_goods_shipment_goods (goods_name);

create index last_updated_time
    on multi_goods_shipment_goods (last_updated_time);

create index order_id
    on multi_goods_shipment_goods (order_id);

create index original_order_goods_id
    on multi_goods_shipment_goods (original_order_goods_id);

create index original_shipment_id
    on multi_goods_shipment_goods (original_shipment_id);

create index outer_goods_id
    on multi_goods_shipment_goods (outer_goods_id);

create index outer_id
    on multi_goods_shipment_goods (outer_id);

create index platform_goods_id
    on multi_goods_shipment_goods (platform_goods_id);

create index platform_sku_id
    on multi_goods_shipment_goods (platform_sku_id);

create index shipment_id
    on multi_goods_shipment_goods (shipment_id);

create index shipment_status
    on multi_goods_shipment_goods (shipment_status);

create index shop_id
    on multi_goods_shipment_goods (shop_id);

create index sku_alias
    on multi_goods_shipment_goods (sku_alias);

create index sku_id
    on multi_goods_shipment_goods (sku_id);

create table oauth
(
    oauth_id            int unsigned auto_increment comment 'id'
        primary key,
    platform_name       varchar(30)                            not null comment '平台',
    facility_id         int unsigned                           not null,
    platform_app_key    varchar(128) default ''                not null comment '平台应用id',
    platform_user_id    varchar(32)                            null comment '平台用户Id',
    platform_user_nick  varchar(120)                           null comment '平台用户昵称',
    access_token        varchar(64)                            null comment '未添加到系统内的店铺，取access_token，添加到系统内的店铺，取shop表对应都access_token',
    expire_time         datetime                               null comment '过期时间',
    last_oauth_time     datetime                               null,
    enabled             tinyint(1)   default 1                 null,
    refresh_token       varchar(64)                            null comment '对于拼多多，需要定期刷新shop_id为空都token',
    expire_alert_ignore tinyint(1)                             null comment '过期提醒，默认null',
    is_alter_add        tinyint(1)   default 1                 null comment '弹窗提醒添加快递模板',
    created_time        datetime     default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time   timestamp    default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint platform_name_key_user_id
        unique (platform_name, platform_app_key, platform_user_id, facility_id)
)
    charset = utf8;

create index created_time
    on oauth (created_time);

create index enabled
    on oauth (enabled);

create index expire_time
    on oauth (expire_time);

create index facility_id
    on oauth (facility_id);

create index last_oauth_time
    on oauth (last_oauth_time);

create index last_updated_time
    on oauth (last_updated_time);

create index platform_user_id
    on oauth (platform_user_id);

create table oauth_share_mailnos
(
    oauth_share_mailnos_id bigint unsigned auto_increment
        primary key,
    oauth_share_id         int(10)                               not null,
    facility_id            int(10)                               not null comment '分享者',
    to_facility_id         int(10)                               not null comment '使用者',
    user_name              varchar(60)                           not null comment '使用者的主账号user_name',
    pdd_branch_code        varchar(128)                          null,
    pdd_branch_name        varchar(128)                          null,
    shipping_id            int(10)                               null,
    tracking_number        varchar(50)                           null,
    status                 varchar(16) default 'INIT'            null comment 'INIT/CANCEL 回收',
    created_time           datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time      timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '分享者看使用者的获取记录' charset = utf8;

create index created_time
    on oauth_share_mailnos (created_time);

create index facility_id
    on oauth_share_mailnos (facility_id);

create index oauth_share_id
    on oauth_share_mailnos (oauth_share_id);

create index status
    on oauth_share_mailnos (status);

create index to_facility_id
    on oauth_share_mailnos (to_facility_id);

create index tracking_number
    on oauth_share_mailnos (tracking_number);

create table order_action
(
    order_action_id   bigint unsigned auto_increment
        primary key,
    order_sn          varchar(32)                         not null,
    facility_id       mediumint(8)                        not null,
    note              varchar(1000)                       not null,
    created_time      datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '订单操作记录表' charset = utf8;

create index created_time
    on order_action (created_time);

create index facility_id
    on order_action (facility_id);

create index last_updated_time
    on order_action (last_updated_time);

create index order_sn
    on order_action (order_sn);

create table order_goods
(
    order_goods_id          bigint unsigned auto_increment
        primary key,
    platform_order_goods_sn varchar(32)                                  null,
    shop_id                 mediumint(8)                                 not null,
    facility_id             int(10)                                      not null,
    order_id                bigint unsigned                              not null comment '订单号',
    platform_sku_id         bigint                                       null,
    sku_id                  bigint                                       null,
    platform_goods_id       bigint                                       null,
    goods_id                bigint                                       null,
    goods_name              varchar(256)                                 not null comment '商品名',
    style_value             varchar(256)                                 null comment '样式名-组合',
    goods_number            mediumint unsigned default 1                 not null comment '商品数量',
    created_time            datetime           default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time       timestamp          default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    price_dot               decimal(10, 2)     default 0.00              not null comment '商品原价',
    discount_amount         decimal(10, 2)                               null comment '折扣费',
    goods_amount            decimal(10, 2)                               null comment '商品总金额= price_dot-discount_amount'
)
    comment '订单商品表' charset = utf8;

create index created_time
    on order_goods (created_time);

create index facility_id
    on order_goods (facility_id);

create index goods_id
    on order_goods (goods_id);

create index last_updated_time
    on order_goods (last_updated_time);

create index order_id
    on order_goods (order_id);

create index platform_goods_id
    on order_goods (platform_goods_id);

create index platform_sku_id
    on order_goods (platform_sku_id);

create index shop_id
    on order_goods (shop_id);

create index sku_id
    on order_goods (sku_id);

create table order_info
(
    order_id                 bigint unsigned auto_increment
        primary key,
    order_sn                 varchar(32)                                     null,
    platform_order_sn        varchar(32)                                     null,
    created_user             varchar(30)    default 'OPENAPI'                null,
    platform_name            varchar(32)                                     not null,
    shop_id                  mediumint(8)                                    not null,
    facility_id              int(10)                                         not null,
    order_status             char(32)       default 'WAIT_SELLER_SEND_GOODS' not null comment '0-UN_CONFIRM 未确认,1-WAIT_SELLER_SEND_GOODS 等待卖家发货, 2-WAIT_BUYER_CONFIRM_GOODS 等待买家确认收货,3-TRADE_FINISHED：交易成功, -1 -TRADE_CLOSED：交易关闭',
    pay_status               char(32)       default 'PS_PAYED'               not null comment '0-PS_UNPAYED,2-PS_PAYED,3-PS_REFUND_APPLY,4-PS_REFUNDING，5-PS_REFUND_SUCC ',
    pay_time                 datetime                                        null comment '付款时间（yyyy-MM-dd HH:mm:ss）',
    confirm_time             datetime                                        null comment '确认时间（yyyy-MM-dd HH:mm:ss）',
    refund_time              datetime                                        null,
    refund_apply_time        datetime                                        null,
    is_refund_ignore         tinyint(1)     default 0                        null,
    shipping_time            datetime                                        null comment '发货时间（yyyy-MM-dd HH:mm:ss）',
    shipping_due_time        datetime                                        null comment '应发时间',
    received_time            datetime                                        null comment '收货确认时间（yyyy-MM-dd HH:mm:ss）',
    goods_amount             decimal(10, 2) default 0.00                     null comment '商品总金额',
    shipping_amount          decimal(10, 2) default 0.00                     null comment '运费',
    order_amount             decimal(10, 2) default 0.00                     null comment '应付金额 = goods_amount + shipping_amount',
    discount_amount          decimal(10, 2) default 0.00                     null comment '折扣费',
    platform_discount_amount decimal(10, 2) default 0.00                     null,
    pay_amount               decimal(10, 2) default 0.00                     null comment '实际支付费 = order_amount - discount_amount',
    buyer_note               varchar(512)                                    null comment '买家备注',
    seller_note              varchar(512)                                    null comment '卖家备注',
    shipping_name            varchar(128)                                    null,
    buyer_id                 bigint                                          null comment '买家id',
    address_id               bigint(16) unsigned                             not null,
    shipment_id              bigint unsigned                                 null,
    created_time             datetime       default CURRENT_TIMESTAMP        null comment '创建时间',
    last_updated_time        timestamp      default CURRENT_TIMESTAMP        not null on update CURRENT_TIMESTAMP,
    constraint u_platform_name_sn_key
        unique (platform_name, order_sn)
)
    comment '订单表' charset = utf8;

create index address_id
    on order_info (address_id);

create index confirm_time
    on order_info (confirm_time);

create index created_time
    on order_info (created_time);

create index created_user
    on order_info (created_user);

create index facility_id
    on order_info (facility_id);

create index goods_amount
    on order_info (goods_amount);

create index is_refund_ignore
    on order_info (is_refund_ignore);

create index last_updated_time
    on order_info (last_updated_time);

create index order_sn
    on order_info (order_sn);

create index order_status
    on order_info (order_status);

create index pay_status
    on order_info (pay_status);

create index platform_order_sn
    on order_info (platform_order_sn);

create index received_time
    on order_info (received_time);

create index refund_time
    on order_info (refund_time);

create index shipment_id
    on order_info (shipment_id);

create index shipping_due_time
    on order_info (shipping_due_time);

create index shipping_time
    on order_info (shipping_time);

create index shop_id
    on order_info (shop_id);

create table package
(
    package_id        bigint unsigned auto_increment
        primary key,
    shipping_id       int unsigned                               default 0                 null,
    tracking_number   varchar(50)                                                          null,
    package_type      enum ('SINGLE_SHIPMENT', 'MULTI_SHIPMENT') default 'SINGLE_SHIPMENT' null comment 'SINGLE_SHIPMENT 单发货单,MULTI_SHIPMENT 多发货单订单',
    facility_id       int(10)                                                              not null,
    created_time      datetime                                   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp                                  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint shipping_tracking_number
        unique (shipping_id, tracking_number)
)
    comment '发货表' charset = utf8;

create index created_time
    on package (created_time);

create index facility_id
    on package (facility_id);

create index last_updated_time
    on package (last_updated_time);

create index shipping_id
    on package (shipping_id);

create index tracking_number
    on package (tracking_number);

create table pdd_refund
(
    id                 bigint unsigned auto_increment
        primary key,
    order_sn           varchar(32)                              not null comment '订单号',
    after_sales_id     int                                      not null comment '售后单id',
    facility_id        int unsigned                             null,
    platform_shop_id   char(32)                                 null,
    after_sales_reason varchar(256)                             null comment '售后原因',
    after_sales_status int(4)                                   not null comment '售后状态 1：全部 2：买家申请退款，待商家处理 3：退货退款，待商家处理 4：商家同意退款，退款中 5：平台同意退款，退款中 6：驳回退款， 待买家处理 7：已同意退货退款,待用户发货 8：平台处理中 9：平台拒 绝退款，退款关闭 10：退款成功 11：买家撤销 12：买家逾期未处 理，退款失败 13：买家逾期，超过有效期 14 : 换货补寄待商家处理 15:换货补寄待用户处理 16:换货补寄成功 17:换货补寄失败 18:换货补寄待用户确认完成; 31：商家同意拒收退款，待用户拒收；32： 待商家补寄发货',
    after_sales_type   tinyint(1)                               null comment '售后类型 1-仅退款，2-退货退款，3-换货，4-补寄，5-维修',
    confirm_time       datetime                                 null comment '订单成团时间',
    discount_amount    decimal(10, 2) default 0.00              null comment '订单折扣金额',
    express_no         varchar(64)                              null comment '退货物流单号',
    goods_number       mediumint(8)                             null comment '商品数量',
    goods_price        decimal(10, 2) default 0.00              null comment '商品单价',
    order_amount       decimal(10, 2) default 0.00              null comment '交易金额 ',
    recreated_at       datetime                                 null comment '售后单创建时间（重新申请时间）',
    refund_amount      decimal(10, 2) default 0.00              null comment '退款金额',
    shipping_status    tinyint(1)                               null comment '订单发货状态 0:未发货， 1:已发货（包含：已发货，已揽收）',
    speed_refund_flag  tinyint(1)                               null comment '极速退款标志位 1：极速退款，0：非极速退款',
    expire_time        datetime                                 null comment '售后逾期时间（只提供待商家处理状态下的，其余的状态为null）',
    created_time       datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time  timestamp      default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint order_sn
        unique (order_sn)
)
    comment '售后单详情' charset = utf8;

create index after_sales_id
    on pdd_refund (after_sales_id);

create index after_sales_status
    on pdd_refund (after_sales_status);

create index after_sales_type
    on pdd_refund (after_sales_type);

create index confirm_time
    on pdd_refund (confirm_time);

create index expire_time
    on pdd_refund (expire_time);

create index express_no
    on pdd_refund (express_no);

create index facility_id
    on pdd_refund (facility_id);

create index platform_shop_id
    on pdd_refund (platform_shop_id);

create index recreated_at
    on pdd_refund (recreated_at);

create index shipping_status
    on pdd_refund (shipping_status);

create index speed_refund_flag
    on pdd_refund (speed_refund_flag);

create table platform_goods
(
    id                bigint unsigned auto_increment
        primary key,
    facility_id       int unsigned                          not null,
    shop_id           mediumint unsigned                    not null,
    platform_goods_id bigint unsigned                       null,
    goods_name        varchar(300)                          not null comment '商品名称',
    platform_code     char(32)    default 'pinduoduo'       not null comment 'pinduoduo 拼多多，taobao 淘宝，jd 京东',
    image_url         varchar(256)                          null comment '商品图片url',
    goods_quantity    int                                   null comment '商品库存',
    is_onsale         bigint(1)                             null comment '是否下架',
    is_delete         tinyint(1)  default 0                 null,
    outer_id          varchar(50)                           null comment '商家编码',
    is_inventory      tinyint(1)  default 1                 null comment '0-关闭库存，1-开启库存',
    has_sku_spec      tinyint(1)                            null,
    mapping_status    varchar(30) default 'TODO'            null comment 'TODO 未匹配，DOING 未完全匹配，DONE 已匹配',
    created_time      datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint u_shop_goods_id_key
        unique (shop_id, platform_goods_id)
)
    comment '商品表' charset = utf8;

create index created_time
    on platform_goods (created_time);

create index facility_id
    on platform_goods (facility_id);

create index is_delete
    on platform_goods (is_delete);

create index is_onsale
    on platform_goods (is_onsale);

create index last_updated_time
    on platform_goods (last_updated_time);

create index mapping_status
    on platform_goods (mapping_status);

create index outer_id
    on platform_goods (outer_id);

create index platform_code
    on platform_goods (platform_code);

create index platform_goods_id
    on platform_goods (platform_goods_id);

create index shop_id
    on platform_goods (shop_id);

create table platform_sku
(
    id                bigint unsigned auto_increment
        primary key,
    facility_id       int unsigned                         not null,
    shop_id           mediumint(8)                         not null,
    platform_goods_id bigint unsigned                      null,
    platform_sku_id   bigint unsigned                      null,
    platform_code     char(32)   default 'pinduoduo'       not null comment 'pinduoduo 拼多多，taobao 淘宝，jd 京东',
    is_group          tinyint(1) default 0                 null,
    spec              varchar(500)                         null comment '商品描叙',
    spec_1_id         int(10)                              null,
    spec_1_key        varchar(64)                          null,
    spec_1_value      varchar(64)                          null,
    spec_2_id         int(10)                              null,
    spec_2_key        varchar(64)                          null,
    spec_2_value      varchar(64)                          null,
    spec_3_id         int(10)                              null,
    spec_3_key        varchar(64)                          null,
    spec_3_value      varchar(64)                          null,
    sku_quantity      int(10)                              null comment '商品库存',
    outer_id          varchar(50)                          null comment '外部编码',
    is_onsale         tinyint(1)                           null,
    is_delete         tinyint(1) default 0                 null,
    sku_img           varchar(256)                         null comment '商品图片',
    created_time      datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint u_shop_sku_id_key
        unique (shop_id, platform_goods_id, platform_sku_id)
)
    comment '平台sku表' charset = utf8;

create index facility_id
    on platform_sku (facility_id);

create index is_delete
    on platform_sku (is_delete);

create index is_group
    on platform_sku (is_group);

create index is_onsale
    on platform_sku (is_onsale);

create index last_updated_time
    on platform_sku (last_updated_time);

create index platform_goods_id
    on platform_sku (platform_goods_id);

create index platform_sku_id
    on platform_sku (platform_sku_id);

create index shop_id
    on platform_sku (shop_id);

create table print_log
(
    id                            bigint unsigned auto_increment
        primary key,
    order_id                      bigint unsigned                                              not null,
    shipment_id                   bigint unsigned                                              not null,
    platform_order_sn             varchar(32)                                                  null,
    platform_name                 varchar(30)                                                  null,
    print_type                    enum ('TRACKINGNUMBER', 'WAYBILL') default 'TRACKINGNUMBER'  null comment 'TRACKINGNUMBER 打印面单,WALLBILL 打印发货单,',
    shipping_id                   int unsigned                       default 0                 null,
    facility_shipping_template_id int(10)                                                      null,
    shipping_name                 varchar(120)                       default '未选择快递'           not null,
    tracking_number               varchar(50)                                                  null,
    mailnos_id                    bigint unsigned                    default 0                 null comment 'mailnos 的id',
    province_id                   smallint(5)                                                  not null comment '收件人所在省id',
    province_name                 varchar(64)                                                  not null comment '收件人所在省，如浙江省、北京',
    city_id                       smallint(5)                                                  not null comment '收件人所在市id',
    city_name                     varchar(64)                                                  not null comment '收件人所在市，如杭州市、上海市',
    district_id                   smallint(5)                                                  not null comment '收件人所在县id',
    district_name                 varchar(64)                                                  not null comment '收件人所在县（区）',
    town_name                     varchar(32)                                                  null comment '收件人所在镇（乡）',
    shipping_address              varchar(256)                                                 not null comment '收件人详细地址，不包含省市',
    receive_name                  varchar(64)                                                  not null comment '收件人姓名',
    mobile                        varchar(16)                                                  not null comment '收件人移动电话',
    print_user                    varchar(128)                                                 not null comment '收件人移动电话',
    created_time                  datetime                           default CURRENT_TIMESTAMP null comment '创建时间',
    facility_id                   int unsigned                       default 0                 null comment '发货地（仓）',
    warehouse_id                  int(10)                                                      null,
    shop_id                       mediumint(8)                                                 not null,
    batch_sn                      varchar(32)                                                  null,
    thermal_type                  varchar(30)                        default 'THERMAL'         not null,
    batch_order                   int(10)                            default 1                 null,
    tactics_id                    int(10)                                                      null,
    tactics_name                  varchar(64)                                                  null,
    print_data                    text                                                         null,
    print_source                  varchar(30)                        default 'SINGLE'          null comment 'SINGLE 正常打印 MULTI 追加面单 SHIPPED 已发货打印 MANU 手工订单 EXCEPTION 异常订单打印 PRINT_LOG打印日志'
)
    comment '打印日志表' charset = utf8;

create index batch_sn
    on print_log (batch_sn);

create index city_id
    on print_log (city_id);

create index created_time
    on print_log (created_time);

create index district_id
    on print_log (district_id);

create index facility_id
    on print_log (facility_id);

create index facility_shipping_template_id
    on print_log (facility_shipping_template_id);

create index mailnos_id
    on print_log (mailnos_id);

create index mobile
    on print_log (mobile);

create index order_id
    on print_log (order_id);

create index platform_name
    on print_log (platform_name);

create index platform_order_sn
    on print_log (platform_order_sn);

create index print_type
    on print_log (print_type);

create index province_id
    on print_log (province_id);

create index receive_name
    on print_log (receive_name);

create index shipment_id
    on print_log (shipment_id);

create index shipping_id
    on print_log (shipping_id);

create index shop_id
    on print_log (shop_id);

create index tactics_id
    on print_log (tactics_id);

create index thermal_type
    on print_log (thermal_type);

create index tracking_number
    on print_log (tracking_number);

create index warehouse_id
    on print_log (warehouse_id);

create table print_log_extension_encrypt
(
    batch_sn                     varchar(32)                         not null,
    batch_order                  int(10)                             not null,
    facility_id                  int(10)                             not null,
    encrypt_shop_id              int(10)                             not null,
    mobile_encrypt               varchar(256)                        null comment '手机号密文原串',
    mobile_search_text           varchar(32)                         null comment '手机号检索串',
    receive_name_encrypt         varchar(512)                        null comment '收件人密文原串',
    receive_name_search_text     varchar(128)                        null comment '收件人检索串',
    shipping_address_encrypt     varchar(1024)                       null comment '详细地址密文原串',
    shipping_address_search_text varchar(1024)                       null comment '详细地址检索串',
    aid                          varchar(512)                        null comment '淘宝的OAID和1688的CAID',
    created_time                 datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time            timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    receive_name_mask            varchar(64)                         null comment '收件人姓名',
    mobile_mask                  varchar(16)                         null comment '收件人移动电话',
    shipping_address_mask        varchar(256)                        null comment '收件人详细地址，不包含省市',
    encrypt_platform_order_sn    varchar(64)                         null,
    primary key (batch_sn, batch_order, facility_id)
)
    comment '打印日志加密扩展表' charset = utf8;

create index encrypt_shop_id
    on print_log_extension_encrypt (encrypt_shop_id);

create index facility_id
    on print_log_extension_encrypt (facility_id);

create index mobile_mask
    on print_log_extension_encrypt (mobile_mask);

create index mobile_search_text
    on print_log_extension_encrypt (mobile_search_text);

create index receive_name_search_text
    on print_log_extension_encrypt (receive_name_search_text);

create index shipping_address_search_text
    on print_log_extension_encrypt (shipping_address_search_text);

create table region
(
    region_id          smallint unsigned auto_increment
        primary key,
    parent_id          smallint unsigned   default 0  not null,
    region_name        varchar(64)         default '' not null,
    region_type        tinyint(1)          default 2  not null,
    area_name          varchar(30)                    null,
    status             tinyint(1) unsigned default 1  null,
    simple_region_name varchar(64)         default '' null
)
    charset = utf8;

create index area_name
    on region (area_name);

create index parent_id
    on region (parent_id);

create index region_type
    on region (region_type);

create table region_key
(
    region_key_id     bigint unsigned auto_increment
        primary key,
    region_key_name   varchar(100)        default ''                null,
    facility_id       int unsigned                                  not null,
    region_json       text                                          null comment '[{"province_id":"", "city_id":"", "district_id":""}]',
    `keys`            varchar(64)                                   not null,
    is_like           tinyint(1)          default 1                 null,
    status            tinyint(1) unsigned default 1                 null,
    created_time      datetime            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on region_key (created_time);

create index facility_id
    on region_key (facility_id);

create index last_updated_time
    on region_key (last_updated_time);

create index region_key_name
    on region_key (region_key_name);

create index status
    on region_key (status);

create table region_lable
(
    region_lable_id   bigint unsigned auto_increment
        primary key,
    region_lable_name varchar(64)                                   null,
    facility_id       int unsigned                                  null,
    region_ids        varchar(128)                                  null,
    region_names      varchar(255)                                  not null,
    status            tinyint(1) unsigned default 1                 null,
    is_like           tinyint(1)          default 1                 null,
    created_time      datetime            default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on region_lable (created_time);

create index facility_id
    on region_lable (facility_id);

create index last_updated_time
    on region_lable (last_updated_time);

create index status
    on region_lable (status);

create table region_mapping
(
    region_id          smallint unsigned auto_increment
        primary key,
    parent_id          smallint unsigned   default 0  not null,
    region_name        varchar(64)                    null,
    simple_region_name varchar(64)         default '' not null,
    region_type        tinyint(1)          default 2  not null,
    status             tinyint(1) unsigned default 1  null
)
    charset = utf8;

create index parent_id
    on region_mapping (parent_id);

create index region_name
    on region_mapping (region_name);

create index region_type
    on region_mapping (region_type);

create index simple_region_name
    on region_mapping (simple_region_name);

create table shipment
(
    shipment_id          bigint unsigned auto_increment
        primary key,
    platform_name        varchar(32)                                                                       not null,
    order_sn             varchar(32)                                                                       null,
    platform_order_sn    varchar(32)                                                                       null,
    created_user         varchar(30)                                             default 'OPENAPI'         null,
    shop_id              mediumint(8)                                                                      not null,
    shipment_status      char(32)                                                default 'WAIT_SHIP'       null comment 'WAIT_SHIP 未发货,SHIPPED 已发货',
    is_print_tracking    tinyint(1)                                              default 0                 null comment '面单是否打印',
    is_print_waybill     tinyint(1)                                              default 0                 null comment '发货单是否打印',
    status               enum ('CONFIRM', 'DELETED', 'STOP', 'CANCEL')           default 'CONFIRM'         null comment 'CONFIRM 确认,CANCEL 取消,DELETED 被合并作废，STOP 合并订单中有订单取消',
    shipment_type        enum ('SINGLE_PRODUCT', 'MULTI_PRODUCT', 'MULTI_ORDER') default 'SINGLE_PRODUCT'  null comment 'SINGLE_PRODUCT 单品聚划算订单,MULTI_PRODUCT 多品购物车订单,MULTI_ORDER 多个订单合并的发货单',
    order_count          int unsigned                                            default 1                 null comment '订单数',
    sku_type_count       int unsigned                                            default 1                 null comment 'sku 种类数',
    sku_count            int unsigned                                            default 1                 null comment 'sku 商品数',
    shipping_id          int unsigned                                            default 0                 null,
    shipping_name        varchar(120)                                            default '未选择快递'           null,
    tracking_number      varchar(50)                                                                       null,
    address_id           bigint unsigned                                         default 0                 null,
    province_id          smallint(5)                                                                       not null comment '收件人所在省id',
    province_name        varchar(64)                                                                       not null comment '收件人所在省，如浙江省、北京',
    city_id              smallint(5)                                                                       not null comment '收件人所在市id',
    city_name            varchar(64)                                                                       not null comment '收件人所在市，如杭州市、上海市',
    district_id          smallint(5)                                                                       not null comment '收件人所在县id',
    district_name        varchar(64)                                                                       not null comment '收件人所在县（区）',
    town_name            varchar(32)                                                                       null comment '收件人所在镇（乡）',
    shipping_address     varchar(256)                                                                      not null comment '收件人详细地址，不包含省市',
    receive_name         varchar(64)                                                                       not null comment '收件人姓名',
    mobile               varchar(16)                                                                       not null comment '收件人移动电话',
    facility_id          int unsigned                                            default 0                 null comment '发货地（仓）',
    shipping_time        datetime                                                                          null comment '发货时间',
    confirm_time         datetime                                                                          null comment '确认时间（yyyy-MM-dd HH:mm:ss）',
    shipping_due_time    datetime                                                                          null comment '应发时间',
    print_time           datetime                                                                          null comment '打印时间',
    shipping_self_weight decimal(10, 4)                                          default 0.0000            null comment '出库时称重',
    shipping_out_weight  decimal(10, 4)                                          default 0.0000            null comment '快递公司称重',
    created_time         datetime                                                default CURRENT_TIMESTAMP null comment '创建时间',
    original_order_id    bigint unsigned                                                                   null comment '原始订单号',
    last_updated_time    timestamp                                               default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    shipping_user        varchar(128)                                                                      null comment '发件人，其他平台发的，openapi',
    tms_accept_time      datetime                                                                          null comment 'tms 揽收时间'
)
    comment '发货表' charset = utf8;

create index address_id
    on shipment (address_id);

create index city_id
    on shipment (city_id);

create index confirm_time
    on shipment (confirm_time);

create index created_time
    on shipment (created_time);

create index created_user
    on shipment (created_user);

create index district_id
    on shipment (district_id);

create index facility_id
    on shipment (facility_id);

create index idx_shipping_time
    on shipment (shipping_time);

create index is_print_tracking
    on shipment (is_print_tracking);

create index is_print_waybill
    on shipment (is_print_waybill);

create index last_updated_time
    on shipment (last_updated_time);

create index mobile
    on shipment (mobile);

create index order_sn
    on shipment (order_sn);

create index original_order_id
    on shipment (original_order_id);

create index platform_name
    on shipment (platform_name);

create index platform_order_sn
    on shipment (platform_order_sn);

create index province_id
    on shipment (province_id);

create index receive_name
    on shipment (receive_name);

create index shipment_status
    on shipment (shipment_status);

create index shipment_type
    on shipment (shipment_status);

create index shipping_due_time
    on shipment (shipping_due_time);

create index shipping_id
    on shipment (shipping_id);

create index shop_id
    on shipment (shop_id);

create index status
    on shipment (status);

create index tracking_number
    on shipment (tracking_number);

create table shipment_delivery_fail
(
    id                int unsigned auto_increment
        primary key,
    facility_id       int unsigned                         not null,
    shipment_id       bigint unsigned                      not null,
    is_ignore         tinyint(1) default 0                 null comment '是否忽略',
    msg               varchar(512)                         null,
    created_time      datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index facility_id
    on shipment_delivery_fail (facility_id);

create index is_ignore
    on shipment_delivery_fail (is_ignore);

create index shipment_id
    on shipment_delivery_fail (shipment_id);

create table shipment_exception_flag
(
    id                bigint unsigned auto_increment
        primary key,
    shipment_id       bigint unsigned                      not null,
    platform_name     varchar(32)                          not null,
    order_sn          varchar(512)                         not null,
    shop_id           mediumint(8)                         not null,
    facility_id       int unsigned                         null,
    exception_type    varchar(30)                          not null comment 'printedUpdateAddresss/printedRefund',
    is_system_fixed   tinyint(1) default 0                 null,
    is_user_fixed     tinyint(1) default 0                 null,
    created_time      datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment '异常订单' charset = utf8;

create index created_time
    on shipment_exception_flag (created_time);

create index facility_id
    on shipment_exception_flag (facility_id);

create index is_system_fixed
    on shipment_exception_flag (is_system_fixed);

create index is_user_fixed
    on shipment_exception_flag (is_user_fixed);

create index order_sn
    on shipment_exception_flag (order_sn);

create index platform_name
    on shipment_exception_flag (platform_name);

create index shipment_id
    on shipment_exception_flag (shipment_id);

create index shop_id
    on shipment_exception_flag (shop_id);

create table shipment_package
(
    id                bigint unsigned auto_increment
        primary key,
    package_id        bigint unsigned                       not null,
    shipment_id       bigint unsigned                       not null,
    status            varchar(30) default 'OK'              null comment 'OK 状态，cancel 发货单取消，合并取消原发货单',
    is_print_tracking tinyint(1)  default 0                 null comment '面单是否打印',
    print_time        datetime                              null,
    facility_id       int(10)                               not null,
    created_time      datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint facility_id_package_id
        unique (facility_id, package_id),
    constraint package_id_2
        unique (package_id, shipment_id)
)
    comment '发货包裹表' charset = utf8;

create index created_time
    on shipment_package (created_time);

create index facility_id
    on shipment_package (facility_id);

create index is_print_tracking
    on shipment_package (is_print_tracking);

create index last_updated_time
    on shipment_package (last_updated_time);

create index package_id
    on shipment_package (package_id);

create index print_time
    on shipment_package (print_time);

create index shipment_id
    on shipment_package (shipment_id);

create index shipment_status
    on shipment_package (shipment_id, status);

create index status
    on shipment_package (status);

create table shipping
(
    shipping_id                  int unsigned auto_increment
        primary key,
    shipping_name                varchar(120)        default '' not null,
    shipping_code                varchar(30)                    null comment '快递编码',
    send_order_url               varchar(256)                   null comment '推送订单url',
    get_station_url              varchar(256)                   null comment '获取走件信息的URL',
    enabled                      tinyint(1) unsigned default 0  not null,
    is_hide                      tinyint(1)          default 0  null,
    support_thermal              tinyint(1)          default 1  null,
    support_normal               tinyint(1)          default 1  null comment '是否支持针孔',
    support_cainiao              tinyint(1)          default 1  null comment '是否支持菜鸟',
    support_pdd                  tinyint(1)          default 0  null comment '是否支持pdd',
    support_kuaidiniao           tinyint(1)          default 0  null comment '是否支持快递鸟',
    support_pdd_logistic_service tinyint(1)          default 0  null comment '是否支持pdd增值服务',
    route_user_id                varchar(50)                    null comment '获取路由账户id',
    route_user                   varchar(50)                    null comment '获取路由账户',
    route_password               varchar(50)                    null comment '获取路由密码',
    route_sign                   varchar(50)                    null comment '获取路由秘钥',
    taobao_shipping_id           varchar(20)                    null comment 'taobao上的快递ID',
    thermal_template_url         varchar(256)                   null comment '菜鸟电子面单url',
    normal_template_url          varchar(256)                   null comment '菜鸟针孔打印模板url',
    pdd_template_url             varchar(256)                   null comment '拼多多电子面单url',
    thermal_image_url            varchar(256)                   null comment '热敏大图',
    thermal_thumb_url            varchar(256)                   null comment '热敏小图',
    normal_image_url             varchar(256)                   null comment '针式大图',
    normal_thumb_url             varchar(256)                   null comment '针式小图',
    normal_template_name         varchar(256)                   null comment '针式模板名',
    thermal_template_name        varchar(256)                   null comment '热敏模板名'
)
    charset = utf8;

create index is_hide
    on shipping (is_hide);

create table shipping_fee_template
(
    shipping_fee_template_id int unsigned auto_increment comment 'id'
        primary key,
    facility_id              int unsigned                         not null,
    template_name            varchar(64)                          null,
    template_type            varchar(30)                          not null comment 'piece计件/ weight计重',
    is_default               tinyint(1) default 0                 null,
    created_time             datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index created_time
    on shipping_fee_template (created_time);

create index facility_id
    on shipping_fee_template (facility_id);

create index last_updated_time
    on shipping_fee_template (last_updated_time);

create index template_name
    on shipping_fee_template (template_name);

create index template_type
    on shipping_fee_template (template_type);

create table shipping_fee_template_detail
(
    shipping_fee_template_detail_id int unsigned auto_increment comment 'id'
        primary key,
    shipping_fee_template_id        int unsigned                        not null,
    facility_id                     int unsigned                        not null,
    area_name                       varchar(30)                         null,
    province_id                     int(10)                             null,
    first                           decimal(11, 3)                      null,
    first_fee                       decimal(10, 2)                      null,
    first1                          decimal(11, 3)                      null,
    first_fee1                      decimal(10, 2)                      null,
    first2                          decimal(11, 3)                      null,
    first_fee2                      decimal(10, 2)                      null,
    first3                          decimal(11, 3)                      null,
    first_fee3                      decimal(10, 2)                      null,
    first4                          decimal(11, 3)                      null,
    first_fee4                      decimal(10, 2)                      null,
    second                          decimal(11, 3)                      null,
    second_fee                      decimal(10, 2)                      null,
    second_end                      decimal(11, 3)                      null,
    second1                         decimal(11, 3)                      null,
    second_fee1                     decimal(10, 2)                      null,
    second_end1                     decimal(11, 3)                      null,
    second2                         decimal(11, 3)                      null,
    second_fee2                     decimal(10, 2)                      null,
    second_end2                     decimal(11, 3)                      null,
    second3                         decimal(11, 3)                      null,
    second_fee3                     decimal(10, 2)                      null,
    second_end3                     decimal(11, 3)                      null,
    second4                         decimal(11, 3)                      null,
    second_fee4                     decimal(10, 2)                      null,
    second_end4                     decimal(11, 3)                      null,
    created_time                    datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time               timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index area_name
    on shipping_fee_template_detail (area_name);

create index created_time
    on shipping_fee_template_detail (created_time);

create index facility_id
    on shipping_fee_template_detail (facility_id);

create index last_updated_time
    on shipping_fee_template_detail (last_updated_time);

create index province_id
    on shipping_fee_template_detail (province_id);

create index shipping_fee_template_id
    on shipping_fee_template_detail (shipping_fee_template_id);

create table shipping_template
(
    template_id           int unsigned auto_increment
        primary key,
    shipping_id           int unsigned                                                                                                                     not null,
    template_name         varchar(120)                                 default ''                                                                          not null,
    template_file_name    varchar(120)                                 default ''                                                                          not null,
    image_url             varchar(256)                                                                                                                     null,
    thumb_url             varchar(256)                                                                                                                     null,
    template_type         enum ('NORMAL', 'EXPRESS', 'CAINIAO', 'PDD') default 'NORMAL'                                                                    null comment 'NORMAL 针孔,EXPRESS 快递,CAINIAO 菜鸟, PDD_THERMAL 拼多多热敏',
    template_url          varchar(256)                                                                                                                     not null,
    old_template_url      varchar(256)                                                                                                                     null,
    new_template_url      varchar(256)                                                                                                                     null,
    template_width        int(10)                                      default 100                                                                         null comment '模板宽度',
    template_long         int(10)                                      default 180                                                                         null comment '模板长度',
    standard_waybill_type varchar(20)                                                                                                                      null comment '快递模板类型，对应平台那边',
    created_time          datetime                                     default CURRENT_TIMESTAMP                                                           null comment '创建时间',
    last_updated_time     timestamp                                    default CURRENT_TIMESTAMP                                                           not null on update CURRENT_TIMESTAMP,
    is_default            tinyint(1)                                                                                                                       null comment '是否默认模板',
    custom_template_url   varchar(128)                                 default 'https://lepeen.com/print_model/user_template/pdd-custom-template-0517.xml' not null comment '自定义模板链接',
    code                  varchar(20)                                                                                                                      null comment '平台那边的code',
    template_image_url    varchar(256)                                                                                                                     null comment '模板图片',
    custom_width          int(10)                                                                                                                          null comment '自定义区域的宽度',
    custom_height         int(10)                                                                                                                          null comment '自定义区域的高度',
    custom_start_width    int(10)                                                                                                                          null comment '自定义宽开始',
    customer_start_height int(10)                                                                                                                          null comment '自定义高开始',
    constraint shipping_waybill_type
        unique (shipping_id, template_type, standard_waybill_type)
)
    charset = utf8;

create index shipping_id
    on shipping_template (shipping_id);

create index template_type
    on shipping_template (template_type);

create table shop
(
    shop_id                 mediumint unsigned auto_increment
        primary key,
    platform_shop_id        char(32)                                          not null comment '平台店铺id 拼多多 mall_id',
    platform_shop_secret    varchar(128)                                      not null comment '平台店铺秘钥 拼多多服务市场公共接口设置的secret',
    shop_name               varchar(64)                                       null comment '店铺名称',
    access_token            varchar(500)                                      null comment '''淘宝平台是通过access token 调用接口''',
    app_key                 varchar(128)                                      null,
    created_user            varchar(64)                                       null comment '创建者',
    created_from            varchar(30)         default 'other'               null,
    created_time            datetime            default CURRENT_TIMESTAMP     null comment '创建时间',
    last_updated_time       timestamp           default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    default_facility_id     int unsigned        default 0                     not null comment '默认快递',
    platform_code           char(32)            default 'pinduoduo'           not null comment 'pinduoduo 拼多多，taobao 淘宝，jd 京东',
    is_auto_merge           tinyint(1)          default 0                     not null comment '0 不合并，1自动合并',
    enabled                 tinyint(1) unsigned default 0                     not null,
    is_sync_refund          tinyint(1)          default 0                     null,
    is_notify_pdd           tinyint(1)          default 0                     null comment '是否注册拼多多回调',
    is_notify_shipped       tinyint(1)          default 1                     not null comment '1 发货通知，0 不通知',
    is_open_express_warning tinyint(1)          default 0                     null comment '是否开启物流预警',
    party_id                mediumint unsigned                                not null,
    auto_merge              tinyint(1) unsigned default 1                     not null,
    platform_name           varchar(32)         default 'pinduoduo'           not null comment 'pinduoduo 拼多多,taobao 淘宝,youzan 有赞,jd 京东',
    expire_time             datetime            default '2019-08-18 06:00:00' null,
    last_oauth_time         datetime                                          null,
    pay_expire_time         datetime            default CURRENT_TIMESTAMP     null,
    expire_alert_ignore     tinyint(1)          default 0                     not null comment '过期提醒，默认null，值为1时过期不弹窗',
    app_id                  varchar(128)        default '23708948'            null comment '平台应用id',
    refresh_token           varchar(512)                                      null,
    platform_user_id        varchar(32)                                       null comment '用户Id',
    platform_user_name      varchar(32)                                       null comment '平台授权名',
    major_platform_shop_id  char(32)                                          null,
    refresh_expire_time     datetime            default '2017-01-01 00:00:00' null,
    version                 varchar(20)         default 'dd'                  null comment '店铺的多多打单版本：dd 标准版,advanced 高级版',
    qcode_url               varchar(512)        default ''                    null comment '营销二维码连接',
    login_child_user_id     int unsigned                                      null,
    facility_address_id     int(10)             default 0                     not null,
    constraint platform_shop_id
        unique (platform_shop_id, platform_code)
)
    comment '店铺表' charset = utf8;

create index created_from
    on shop (created_from);

create index created_time
    on shop (created_time);

create index default_facility_id
    on shop (default_facility_id);

create index enabled
    on shop (enabled);

create index expire_alert_ignore
    on shop (expire_alert_ignore);

create index facility_address_id
    on shop (facility_address_id);

create index is_sync_refund
    on shop (is_sync_refund);

create index last_oauth_time
    on shop (last_oauth_time);

create index last_updated_time
    on shop (last_updated_time);

create index major_platform_shop_id
    on shop (major_platform_shop_id);

create index party_id
    on shop (party_id);

create index party_platform_name
    on shop (party_id, platform_name);

create index pay_expire_time
    on shop (pay_expire_time);

create index platform_code
    on shop (platform_code);

create index platform_name
    on shop (platform_name);

create index refresh_expire_time
    on shop (refresh_expire_time);

create table shop_back
(
    shop_id                mediumint unsigned auto_increment
        primary key,
    platform_shop_id       char(32)                                          not null comment '平台店铺id 拼多多 mall_id',
    platform_shop_secret   varchar(128)                                      not null comment '平台店铺秘钥 拼多多服务市场公共接口设置的secret',
    shop_name              varchar(64)                                       null comment '店铺名称',
    access_token           varchar(500)                                      null comment '''淘宝平台是通过access token 调用接口''',
    app_key                varchar(128)                                      null,
    created_user           varchar(64)                                       null comment '创建者',
    created_from           varchar(30)         default 'other'               null,
    created_time           datetime            default CURRENT_TIMESTAMP     null comment '创建时间',
    back_created_time      datetime            default CURRENT_TIMESTAMP     null comment '这才是shop_back的创建时间，那个created_time是原shop的创建时间',
    last_updated_time      timestamp           default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    default_facility_id    int unsigned        default 0                     not null comment '默认快递',
    platform_code          char(32)            default 'pinduoduo'           not null comment 'pinduoduo 拼多多，taobao 淘宝，jd 京东',
    is_auto_merge          tinyint(1)          default 0                     not null comment '0 不合并，1自动合并',
    enabled                tinyint(1) unsigned default 0                     not null,
    is_sync_refund         tinyint(1)          default 0                     null,
    is_notify_pdd          tinyint(1)          default 0                     null comment '是否注册拼多多回调',
    is_notify_shipped      tinyint(1)          default 1                     not null comment '1 发货通知，0 不通知',
    party_id               mediumint unsigned                                not null,
    auto_merge             tinyint(1) unsigned default 1                     not null,
    platform_name          varchar(32)         default 'pinduoduo'           not null comment 'pinduoduo 拼多多,taobao 淘宝,youzan 有赞,jd 京东',
    expire_time            datetime            default '2019-08-18 06:00:00' null,
    last_oauth_time        datetime                                          null,
    pay_expire_time        datetime                                          null,
    expire_alert_ignore    tinyint(1)                                        null comment '过期提醒，默认null，值为1时过期不弹窗',
    app_id                 varchar(128)        default '23708948'            null comment '平台应用id',
    refresh_token          varchar(512)                                      null,
    platform_user_id       varchar(32)                                       null comment '用户Id',
    platform_user_name     varchar(32)                                       null comment '平台授权名',
    major_platform_shop_id char(32)                                          null,
    refresh_expire_time    datetime            default '2017-01-01 00:00:00' null,
    version                varchar(20)         default 'dd'                  null comment '店铺的多多打单版本：dd 标准版,advanced 高级版',
    qcode_url              varchar(512)        default ''                    null comment '营销二维码连接',
    is_deleted_data        tinyint             default 0                     null
)
    comment '店铺表' charset = utf8;

create index created_time
    on shop_back (created_time);

create index default_facility_id
    on shop_back (default_facility_id);

create index enabled
    on shop_back (enabled);

create index expire_alert_ignore
    on shop_back (expire_alert_ignore);

create index is_deleted_data
    on shop_back (is_deleted_data);

create index is_sync_refund
    on shop_back (is_sync_refund);

create index last_updated_time
    on shop_back (last_updated_time);

create index party_id
    on shop_back (party_id);

create index party_platform_name
    on shop_back (party_id, platform_name);

create index platform_code
    on shop_back (platform_code);

create index platform_name
    on shop_back (platform_name);

create index refresh_expire_time
    on shop_back (refresh_expire_time);

create table shop_data_goods
(
    shop_data_goods_id int unsigned auto_increment
        primary key,
    facility_id        int unsigned                        not null,
    shop_id            mediumint unsigned                  not null,
    platform_shop_id   char(32)                            not null,
    count_date         date                                not null,
    order_count        int(10)   default 0                 not null comment '订单数量',
    platform_goods_id  bigint unsigned                     null,
    platform_sku_id    bigint unsigned                     null,
    created_time       datetime  default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time  timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint count_date
        unique (count_date, platform_shop_id, platform_sku_id)
)
    charset = utf8;

create index platform_goods_id
    on shop_data_goods (platform_goods_id);

create index platform_shop_id
    on shop_data_goods (platform_shop_id);

create index platform_sku_id
    on shop_data_goods (platform_sku_id);

create index shop_id
    on shop_data_goods (shop_id);

create table sku
(
    sku_id             bigint unsigned auto_increment
        primary key,
    facility_id        int unsigned                             not null,
    goods_id           bigint unsigned                          not null,
    is_group           tinyint(1)     default 0                 null,
    sku_name           varchar(300)                             null,
    sku_alias          varchar(300)                             null,
    spec_1_key         varchar(64)                              null,
    spec_1_value       varchar(64)                              null,
    spec_2_key         varchar(64)                              null,
    spec_2_value       varchar(64)                              null,
    spec_3_key         varchar(64)                              null,
    spec_3_value       varchar(64)                              null,
    outer_id           varchar(50)                              null comment '外部编码',
    image_url          varchar(256)                             null comment '商品图片',
    is_inventory       tinyint(1)     default 1                 null,
    weight             decimal(11, 3) default 0.000             null comment '重量,g',
    weight_is_set      tinyint(1)     default 0                 null comment '重量是否设置',
    package_fee        decimal(10, 2) default 0.00              null comment '包装耗材',
    package_fee_is_set tinyint(1)     default 0                 null comment '包材费是否设置',
    sku_quantity       int            default 0                 null comment '库存',
    warning_quantity   int            default 0                 null comment '库存警戒值',
    is_onsale          bigint(1)      default 1                 null comment '是否下架',
    is_delete          tinyint(1)     default 0                 null,
    mapping_count      smallint(5)    default 0                 null,
    created_time       datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time  datetime       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_type       varchar(30)    default 'OPENAPI_CREATED' null comment 'OPENAPI_CREATED 同步自动创建，GOODS_MAPPING_CREATED 商品匹配创建，GOODS_MANAGER_CREATED 商品管理创建',
    constraint facility_outer_id
        unique (facility_id, goods_id, outer_id),
    constraint goods_sku_spec
        unique (goods_id, spec_1_key, spec_1_value, spec_2_key, spec_2_value, spec_3_key, spec_3_value)
)
    comment 'sku表' charset = utf8;

create index created_time
    on sku (created_time);

create index facility_id
    on sku (facility_id);

create index goods_id
    on sku (goods_id);

create index is_delete
    on sku (is_delete);

create index is_group
    on sku (is_group);

create index is_onsale
    on sku (is_onsale);

create index last_updated_time
    on sku (last_updated_time);

create index sku_alias
    on sku (sku_alias);

create index spec_1_key
    on sku (spec_1_key);

create index spec_2_key
    on sku (spec_2_key);

create index spec_3_key
    on sku (spec_3_key);

create table sku_back
(
    sku_id            bigint unsigned auto_increment
        primary key,
    facility_id       int unsigned                             not null,
    goods_id          bigint unsigned                          not null,
    sku_name          varchar(300)                             null,
    sku_alias         varchar(300)                             null,
    spec_1_key        varchar(64)                              null,
    spec_1_value      varchar(64)                              null,
    spec_2_key        varchar(64)                              null,
    spec_2_value      varchar(64)                              null,
    spec_3_key        varchar(64)                              null,
    spec_3_value      varchar(64)                              null,
    outer_id          varchar(50)                              null comment '外部编码',
    image_url         varchar(256)                             null comment '商品图片',
    weight            decimal(10, 2) default 0.00              null comment '重量,g',
    package_fee       decimal(10, 2) default 0.00              null comment '包装耗材',
    created_time      datetime       default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time datetime       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    back_created_time datetime       default CURRENT_TIMESTAMP null comment '这才是创建时间，那个created_time是原表的创建时间'
)
    comment 'sku表' charset = utf8;

create index created_time
    on sku_back (created_time);

create index facility_id
    on sku_back (facility_id);

create index goods_id
    on sku_back (goods_id);

create index last_updated_time
    on sku_back (last_updated_time);

create index spec_1_key
    on sku_back (spec_1_key);

create index spec_2_key
    on sku_back (spec_2_key);

create index spec_3_key
    on sku_back (spec_3_key);

create table sku_mapping
(
    sku_mapping_id    bigint unsigned auto_increment
        primary key,
    facility_id       int unsigned                       not null,
    shop_id           bigint                             null,
    platform_goods_id bigint                             null,
    platform_sku_id   bigint                             null,
    goods_id          bigint                             null,
    sku_id            bigint                             null,
    mapping_type      varchar(30)                        not null comment 'OUTER_ID 根据商家编码匹配，SPEC 根据样式规格匹配，USER 根据用户选择匹配，AUTO_CREATED 自动创建',
    created_time      datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint platform_sku_id_shop_id
        unique (platform_sku_id, platform_goods_id, shop_id)
)
    comment 'sku匹配表' charset = utf8;

create index created_time
    on sku_mapping (created_time);

create index facility_id
    on sku_mapping (facility_id);

create index goods_id
    on sku_mapping (goods_id);

create index last_updated_time
    on sku_mapping (last_updated_time);

create index mapping_type
    on sku_mapping (mapping_type);

create index platform_goods_id
    on sku_mapping (platform_goods_id);

create index shop_id
    on sku_mapping (shop_id);

create index sku_id
    on sku_mapping (sku_id);

create table sku_mapping_history
(
    sku_mapping_history_id bigint unsigned auto_increment
        primary key,
    sku_mapping_id         bigint                             null,
    facility_id            int unsigned                       not null,
    shop_id                bigint                             null,
    platform_goods_id      bigint                             null,
    platform_sku_id        bigint                             null,
    goods_id               bigint                             null,
    sku_id                 bigint                             null,
    mapping_type           varchar(30)                        not null comment 'OUTER_ID 根据商家编码匹配，SPEC 根据样式规格匹配，USER 根据用户选择匹配，AUTO_CREATED 自动创建',
    history_created_time   datetime default CURRENT_TIMESTAMP null,
    created_time           datetime default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time      datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    comment 'sku匹配历史记录表' charset = utf8;

create index created_time
    on sku_mapping_history (created_time);

create index facility_id
    on sku_mapping_history (facility_id);

create index goods_id
    on sku_mapping_history (goods_id);

create index last_updated_time
    on sku_mapping_history (last_updated_time);

create index mapping_type
    on sku_mapping_history (mapping_type);

create index platform_goods_id
    on sku_mapping_history (platform_goods_id);

create index platform_sku_id
    on sku_mapping_history (platform_sku_id);

create index shop_id
    on sku_mapping_history (shop_id);

create index sku_id
    on sku_mapping_history (sku_id);

create index sku_mapping_id
    on sku_mapping_history (sku_mapping_id);

create table sync_platform_shipping_mapping
(
    mapping_id             int(10) auto_increment
        primary key,
    platform_name          varchar(30)                          not null comment '平台名称',
    platform_shipping_id   varchar(100)                         not null comment '平台的shippingcode',
    system_shipping_id     int(10)                              not null comment '系统的shippingid',
    deleted                tinyint(1) default 0                 null comment '0 未删除     1 删除',
    created_time           datetime   default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time      timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    platform_shipping_name varchar(100)                         null comment '平台的shippingName',
    constraint u_platform_system
        unique (platform_name, system_shipping_id),
    constraint u_platform_system_key
        unique (platform_name, platform_shipping_id, system_shipping_id)
)
    charset = utf8;

create table task
(
    task_id      int unsigned auto_increment
        primary key,
    facility_id  int(10)                               not null,
    batch_sn     bigint                                not null,
    created_time datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    type         varchar(20) default 'NORMAL'          not null comment '订单类型：NORMAL 普通订单；MANU 手工订单',
    constraint batch_pick_sn
        unique (batch_sn, facility_id)
)
    comment '批拣单表' charset = utf8;

create index batch_sn
    on task (batch_sn);

create index created_time
    on task (created_time);

create index facility_id
    on task (facility_id);

create index type
    on task (type);

create table warehouse
(
    warehouse_id               int unsigned auto_increment
        primary key,
    facility_id                int(10)                               not null,
    warehouse_name             varchar(64) default '默认仓库'            not null,
    province_id                int(10)                               null,
    province_name              varchar(30)                           null,
    city_id                    int(10)                               null,
    city_name                  varchar(30)                           null,
    district_id                int(10)                               null,
    district_name              varchar(30)                           null,
    shipping_address           varchar(30)                           null comment '真实地址',
    postcode                   varchar(8)                            null comment '邮编',
    sender_mobile              varchar(30)                           null comment '发件人电话',
    sender_name                varchar(30)                           null comment '发件人',
    sender_company             varchar(30)                           null,
    best_shipping              varchar(64)                           null comment 'region表示只开了地址，goods表示只开了商品，goods,region表示都开了goods优先,region,goods表示都开了region优先； is_best_shipping这个字段弃用',
    best_shipping_refresh_time datetime                              null comment '智能快递最后刷新时间',
    created_time               datetime    default CURRENT_TIMESTAMP null comment '创建时间',
    last_updated_time          timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP
)
    charset = utf8;

create index facility_id
    on warehouse (facility_id);

