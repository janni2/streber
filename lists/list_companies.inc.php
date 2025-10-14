<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}
# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

/**
 * derived ListBlock-class for listing companies
 *
 * @includedby:     pages/*
 *
 * @author         Thomas Mann
 * @uses:           ListBlock
 * @usedby:
 *
 */

class ListBlock_companies extends ListBlock
{
	public $filters = [];
	public $format;

    public function __construct($args=NULL)
    {
		parent::__construct($args);

        $this->id       ='companies';
		$this->title    =__("related companies");

        $this->add_col( new ListBlockColSelect());
   		$this->add_col( new ListBlockColFormat([
			'key'=>'short',
			'name'=>__("Name Short"),
			'tooltip'=>__("Shortnames used in other lists"),
			'sort'=>0,
			'format'=>'<nobr><a href="index.php?go=companyView&amp;company={?id}">{?short}</a></nobr>'
		]));
   		$this->add_col( new ListBlockCol_CompanyName());
   		$this->add_col( new ListBlockColFormat([
			'key'=>'phone',
			'name'=>__("Phone"),
			'tooltip'=>__("Phone-Number"),
			'format'=>'<nobr>{?phone}</nobr>'
		]));
   		$this->add_col( new ListBlockColLinkExtern([
			'key'=>'homepage',
			'name'=>"Homepage",
		]));
    	$this->add_col( new ListBlockColMethod([
    		'name'=>__("Proj"),
    		'tooltip'=>__("Number of open Projects"),
    		'func'=>'getNumOpenProjects',
            'style'=>'right'
    	]));
    	$this->add_col( new ListBlockColMethod([
    		'name'=>__("People"),
            'id'=>"people",
    		'tooltip'=>__("People working for this person"),
    		'sort'=>0,
            'style'=>'nowrap',
    		'func'=>'getPersonLinks',
    	]));

        /*$this->add_col( new ListBlockCol_ProjectEffortSum);

    	$this->add_col( new ListBlockColMethod(array(
    		'name'=>"Tasks",
    		'tooltip'=>"Number of open Tasks",
    		'sort'=>0,
    		'func'=>'getNumTasks',
            'style'=>'right'
    	)));
   		$this->add_col( new ListBlockColDate(array(
			'key'=>'date_start',
			'name'=>"Opened",
			'tooltip'=>"Day the Project opened",
			'sort'=>0,
		)));
   		$this->add_col( new ListBlockColDate(array(
			'key'=>'date_closed',
			'name'=>"Closed",
			'tooltip'=>"Day the Project state changed to closed",
			'sort'=>0,
		)));
        */

        #---- functions ----
        global $PH;
        $this->add_function(new ListFunction([
            'target'=>$PH->getPage('companyEdit')->id,
            'name'  =>__('Edit company'),
            'id'    =>'companyEdit',
            'icon'  =>'edit',
            'context_menu'=>'submit',
        ]));
        $this->add_function(new ListFunction([
            'target'=>$PH->getPage('companyDelete')->id,
            'name'  =>__('Delete company'),
            'id'    =>'companyDelete',
            'icon'  =>'delete'
        ]));
        $this->add_function(new ListFunction([
            'target'=>$PH->getPage('companyNew')->id,
            'name'  =>__('Create new company'),
            'id'    =>'companyNew',
            'icon'  =>'new',
            'context_menu'=>'submit',
        ]));
		$this->add_function(new ListFunction([
            'target'=>$PH->getPage('itemsAsBookmark')->id,
            'name'  =>__('Mark as bookmark'),
            'id'    =>'itemsAsBookmark',
            'context_menu'=>'submit',
        ]));
		
    }
	
	public function print_automatic()
    {
        global $PH;

        $this->active_block_function = 'list';
      
        $pass= true;
    		
		### add filter options ###
        foreach($this->filters as $f) {
            foreach($f->getQuerryAttributes() as $k=>$v) {
                $this->query_options[$k]= $v;
            }
        }
		
		$companies=Company::getAll($this->query_options);
        $this->render_list($companies);
    }
}


class ListBlockCol_CompanyName extends ListBlockCol
{
    public $key= 'name';
    public $width='90%';

    public function __construct($args=NULL)
    {
        parent::__construct($args);
        $this->name=__('Company','Column header');
        #$this->id='name';
    }


	public function render_tr(&$obj, $style="")
	{
        global $PH;

		if(!isset($obj) || !$obj instanceof Company) {
   			return;
		}
				
		$str= $PH->getLink('companyView',asHtml($obj->name), ['company'=>$obj->id],'item company',true);
		print "<td>{$str}</td>";
	}
}




?>