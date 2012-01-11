<?php
 
/**
 * Generate a url using the current internal uri, but replaces a param with a new value.
 * If the param is not in the current uri's query string, it is added instead.
 *
 * This is useful for a page that uses several filters to record sets,
 * and needs all the filters to work together, instead of blasting each
 * other away when a new link is clicked.
 *
 * <strong>Examples:</strong>
 * <code>
 *  // with current uri => mymodule/myaction?author=10&genre=3
 *
 *  echo link_to('new author', filter_url('author', 5));
 *  // uri when clicked => mymodule/myaction?author=5&genre=3
 *
 *  echo link_to('new genre', filter_url('genre', 1));
 *  // uri when clicked => mymodule/myaction?author=10&genre=1
 *
 *  // with current uri => mymodule/myaction
 *
 *  echo link_to('an author', filter_url('author', 10));
 *  // uri when clicked => mymodule/myaction?author=10
 * </code>
 *
 * @param string the name of the parameter to replace
 * @param string the value to replace the current value with
 * @param boolean use route name
 * @return string the url with the parameter replaced
 * @see link_to
 */
function filter_url($param, $new_value, $with_route_name = false)
{
    // fetch params from query string
    $params = _get_params(_get_query_string());

    // replace param with new value
    $params[$param] = $new_value;
 
    //return _get_uri($with_route_name) . '?' . _build_query_string($params);
    return _build_query_string($params);
}

function url_params()
{
  $params = _get_params(_get_query_string());
  return _build_query_string($params);
}

function full_url($with_route_name = false)
{
  $params = _get_params(_get_query_string());
  return _get_uri($with_route_name) . '?' . _build_query_string($params);
}
 
/**
 * Removes a parameter from the current uri and returns the resulting url.
 *
 * @see filter_url
 */
function remove_filter_url($param, $with_route_name = false)
{
    // fetch params from query string
    $params = _get_params(_get_query_string());
 
    // remove param
    unset($params[$param]);
 
    return _get_uri($with_route_name) . '?' . _build_query_string($params);
}
 
/**
 * Generates an unordered list of links to filter the current record set by.
 * Multiple sets of filter_navigation links will work together, using the current uri.
 *
 * <strong>Examples:</strong>
 * <code>
 *  echo filter_navigation(array(10=>'Jones', 12=>'Smith, J.', 13=>'Darby'), 'author_id', 13);
 *  echo filter_navigation(objects_for_filter($authors), 'author_id', 13);
 * </code>
 *
 * @param array list of key=>value pairs of ids and strings
 * @param string the name of the parameter for this filter
 * @param string the selected id (or null, if none selected)
 * @param string the text to use for the "all" link
 * @see filter_url
 */
function filter_navigation($list, $param, $selected = null, $all_text = 'All')
{
    $html = '';
 
    $html .= content_tag('li', link_to_unless($selected === null, $all_text, remove_filter_url($param)));
    foreach ($list as $key => $value)
    {
        $html .= content_tag('li', link_to_unless($selected == $key, $value, filter_url($param, $key)));
    }
 
    return content_tag('ul', $html);
}
 
/**
 * Generates a simple list from a record set of propel objects.
 * Expects a getId function and a toString function.
 *
 * @param array objects to be converted to a list
 * @see filter_navigation
 */
function objects_for_filter($objects)
{
    $list = array();
 
    foreach ($objects as $object)
    {
        $list[$object->getId()] = $object->toString();
    }
 
    return $list;
}
 
function _get_uri($with_route_name = false)
{
    //$internal_uri = sfRouting::getInstance()->getCurrentInternalUri($with_route_name);
    $internal_uri = sfContext::getInstance()->getRouting()->getCurrentInternalUri();
    $ar = explode('?', $internal_uri);
 
    return ($with_route_name ? '@' : '') . $ar[0];
}
 
function _get_query_string()
{
    $internal_uri = $_SERVER['REQUEST_URI'];
    //$internal_uri = sfRouting::getInstance()->getCurrentInternalUri();
    $ar = explode('?', $internal_uri);
 
    return isset($ar[1]) ? $ar[1] : '';
}
 
function _get_params($query_string)
{
    // parse query string into associative array
    $params = array();
    if ($query_string != '')
    {
        foreach (explode('&', $query_string) as $kvpair)
        {
            list($key, $value) = explode('=', $kvpair);
            $params[$key] = $value;
        }
    }
 
    return $params;
}
 
function _build_query_string($params)
{
    // build list of key=value strings
    $ar = array();
    foreach ($params as $key => $value)
    {
        $ar[] = $key . '=' . $value;
    }
 
    return implode('&', $ar);
}
