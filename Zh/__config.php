<?php
/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-15
 */
/** Zh路径 **/
define ( "Zh_FW_PATH", dirname ( __FILE__ ) );

/** Zh版本 **/
define ( "Zh_VERSION", "2.0" );

/** 作者 **/
define ( "Zh_AUTHOR", "abel.zhou" );

/**
 * 缓存全局设置
 * 缓存类型：
 * memcached、db、file、memory
 **/
define ( 'Zh_CACHE_BACKEND', 'memcached' );
/** 默认Cache Tag 名 **/
//define ( 'Zh_CACHE_DEFAULT_TAG', 'zh_tags' );
/** 默认缓存域 **/
//$serverName = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
define ( 'Zh_CACHE_DEFAULT_DOMAIN', 'sql.demo.com' );
/** 默认缓存class **/
define ( 'Zh_CACHE_DEFAULT_CLASS', 'default' );

/** 缓存时间 (5分钟) **/
define ( 'Zh_CACHE_TIMEOUT', 300 );

/** 文件缓存路径 **/
define ( 'Zh_CACHE_FILE_PATH', './zh_cache/' );
/** 设置数据库是否长联 **/
define('Zh_DAO_ATTR_TIMEOUT',false);
/** 系统启用模式 (测试、开发、发布) **/
define ( 'Zh_SCHEMA_TEST', 'test' );
define ( 'Zh_SCHEMA_LOCAL', 'local' );
define ( 'Zh_SCHEMA_RELEASE', 'rls' );

/** 数据库缓存 "缓存表名" **/
define ( 'Zh_CACHE_TABLE', 'zh_cache' );

define ( 'Zh_DB_ATTR_TIMEOUT', 30 );

/** 是否开启数据库权重 **/
define ( 'Zh_DB_WEIGHT', false );
/** 是否开启分布式Session **/
define ( 'Zh_SESSION_DISPERSED', true );