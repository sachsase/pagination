<?php
/*
Website: www.create-dynamic.com
Author: sasikumar
Created on: April 01,2018
Version: 1.0
Description: Pagination Software
*/
class Pagination {

protected $total_records=0;

protected $per_page=10;

protected $max_shownlinks=10;

protected $ispaginate=TRUE;

protected $previous_text="Previous";

protected $next_text="Next";

protected $base_url='';

protected $query_string='';

protected $active_class='active';

protected $pagerequest_string='page';

protected $show_prev_next_links=TRUE;

protected $prev_next_hide_nolinks=FALSE;

protected $wrapper_tag_open='<ul class="pagination">';

protected $wrapper_tag_close='</ul>';

protected $previous_templates_on='<li class="previous"><a href="%s">%s</a></li>';

protected $previous_templates_off='<li class="previous disabled"><a href="#" >%s</a></li>';

protected $next_templates_on='<li class="next"><a href="%s">%s</a></li>';

protected $next_templates_off='<li class="next disabled"><a href="#">%s</a></li>';

protected $show_first_last_links=FALSE;

protected $first_link_text="First";

protected $last_link_text="Last";

protected $first_last_templates='<li><a href="%s">%s</a></li>';

protected $anchor_templates='<li class="%s"><a href="%s">%d</a></li>';

protected $page_description_show=TRUE;

protected $page_description_templates='<li class="" ><a style="background-color:#F8F8F8; font-weight:bold;">%s</a></li>';

protected $total_page=0;

protected $ispage_querystring=FALSE;

protected $current_page=1;

//private $limitsql_return='';

//private $starting_pagenumber=0;

private $prev_link='';

private $next_link='';

private $first_link='';

private $last_link='';

private $all_links='';

public function __construct($config=array()) {

if(isset($config) && is_array($config) && count($config)>0):
$this->settings($config);
endif;

}

public function settings($config=array()) {

if(!isset($config) || !is_array($config) || count($config)<1)
return false;

foreach($config as $key=>$value) {

if(property_exists($this,$key)) {
$this->$key=$value;
}
}

}

public function get_links() {

$arr=array('ispage'=>FALSE,'link'=>FALSE,'limit'=>'','startno'=>0);

$arr['ispage']=$this->isPaginationCheck();
if($arr['ispage']==TRUE) {
$arr['link']=$this->processPagination();

$arr['limit']=$this->getSqlLimit();

$arr['startno']=$this->getStartingPage();
}
return $arr;

}

function isPaginationCheck() {

$this->total_records=($this->total_records>0)?intval($this->total_records,10):0;
$this->per_page=($this->per_page>0)?intval($this->per_page,10):0;

if($this->total_records<1 || $this->per_page<1) {
$this->ispaginate=FALSE;
return false;
}

$this->total_page=ceil($this->total_records/$this->per_page);

if($this->total_page<2) { $this->ispaginate=FALSE; }

return $this->ispaginate;
}

function processPagination() {

$this->pagerequest_string=($this->pagerequest_string!='')?$this->pagerequest_string:'page';

$this->ispage_querystring=isset($_REQUEST[$this->pagerequest_string])?TRUE:FALSE;

$this->current_page=($this->ispage_querystring==TRUE && $_REQUEST[$this->pagerequest_string]>0 && $_REQUEST[$this->pagerequest_string]<=$this->total_page)?intval($_REQUEST[$this->pagerequest_string],10):1;

if($this->show_prev_next_links==TRUE ) {

$prev_href=$this->base_url."?".$this->pagerequest_string."=".($this->current_page-1)."&".$this->query_string;
$next_href=$this->base_url."?".$this->pagerequest_string."=".($this->current_page+1)."&".$this->query_string;

$this->prev_link='';
if($this->current_page>1 && $this->current_page<=$this->total_page) {
$this->prev_link=sprintf($this->previous_templates_on,$prev_href,$this->previous_text);
}else if($this->prev_next_hide_nolinks===FALSE) {
$this->prev_link=sprintf($this->previous_templates_off,$this->previous_text);
}

$this->next_link='';
if($this->current_page>0 && $this->current_page<>$this->total_page) {
$this->next_link=sprintf($this->next_templates_on,$next_href,$this->next_text);
}else if($this->prev_next_hide_nolinks===FALSE) {
// echo htmlspecialchars($this->next_templates_off);
$this->next_link=sprintf($this->next_templates_off,$this->next_text);
}

}

if($this->show_first_last_links==TRUE ) {
$this->first_link=sprintf($this->first_last_templates,$this->processNumberedLink(1),$this->first_link_text);
$this->last_link=sprintf($this->first_last_templates,$this->processNumberedLink($this->total_page),$this->last_link_text);
}

$start_half=(int)ceil($this->max_shownlinks/2);
$starthalf_minus_curpage=$this->current_page-$start_half;
$starthalf_plus_curpage=$this->current_page+$start_half;

if($this->current_page>$start_half && ($this->current_page-$start_half)<$this->total_page && $this->max_shownlinks<$this->total_page ) {
$start=($starthalf_plus_curpage<=$this->total_page)?($this->current_page+1-$start_half):($this->total_page-$this->max_shownlinks+1);
}else {
$start=1;
}

if($this->current_page>$start_half && $this->max_shownlinks<$this->total_page && ($start+$this->max_shownlinks-1)<$this->total_page) {
$end=($start+$this->max_shownlinks-1);
}
else
$end=(($start+$this->max_shownlinks-1)<=$this->total_page)?($start+$this->max_shownlinks-1):$this->total_page;

$forloop_links='';
for($i=$start;$i<=$end;$i++) {
$active_class=($i==$this->current_page)?$this->active_class:'';
$active_link=$this->processNumberedLink($i);
$forloop_links.=sprintf($this->anchor_templates,$active_class,$active_link,$i);
}

$page_desc_html="";
if($this->page_description_show==TRUE){

$page_desc_html=sprintf($this->page_description_templates,"Page ".$this->current_page." of ".$this->total_page);
}

$this->all_links=$this->wrapper_tag_open.$page_desc_html.$this->prev_link.$this->first_link.$forloop_links.$this->last_link.$this->next_link.$this->wrapper_tag_close;

return $this->all_links;

}

function getSqlLimit() {

return $this->limit_return=($this->ispaginate===TRUE)?("LIMIT ".(($this->current_page-1)*$this->per_page)." , ".$this->per_page):'';

}

function processNumberedLink($pagenum) {
$pagenum=(isset($pagenum) && $pagenum>0)?intval($pagenum,10):1;
return $this->base_url."?".$this->pagerequest_string."=".$pagenum."&".$this->query_string;
}

function getStartingPage() {
return (($this->current_page-1)*$this->per_page);
}

}

?>

<?php

/*

$options_arr=array();
$options_arr['total_records']=100;
$options_arr['per_page']=20;
$options_arr['max_shownlinks']=10;
//$options_arr['show_first_last_links']=TRUE;	
$options_arr['base_url']=SITE_PATH.basename($_SERVER['PHP_SELF']);	
$pagination_obj=new Pagination($options_arr);
$arr=$pagination_obj->get_links();

if($arr['ispage']==true):
echo $arr['link'];
endif;

*/

?>