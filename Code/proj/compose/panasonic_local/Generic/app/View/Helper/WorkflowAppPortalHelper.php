<?php
App::uses('AppHelper', 'View/Helper');
App::uses('PaginatorHelper', 'Helper');

class WorkflowAppPortalHelper extends AppHelper {
    var $helpers = array('Paginator');

    public function submenu_left($activeTab, $isApprover, $countsPerState, $enableReview, $enableAssignedToBuddy, $reviewOnly, $isPostApprover, $readOnly){
        $breadcrumb   = array('#' => array('Workflow', 'active'));
        $itemToUrlMap = array(
                    array('Waiting',        'list_waiting_for_your_action',),
        );
        $itemToUrl_no_post_approver = array(
                    array('In Progress',    'list_being_reviewed',),
        );
        $itemToUrl_review = array(
                    array('Approved',       'list_approved'),
        );
        $itemToUrl_review_approver = array(
                    array('All Approved',   'list_approved_all'),
                    array('All',            'list_all'),
        );
        $itemToUrl_assigned_to_buddy = array(
                    array('Administration', 'list_assigned_to_buddy'),
        );
        $itemToUrl_admin = array(
                    array('All Approved',   'list_approved_all'),
                    array('All',            'list_all'),
        );
        $itemToUrl_no_admin = array(
                    array('Closed',         'list_closed'),
        );
        $itemToUrl_readonly_viewer = array(
                    array('Waiting',            'list_waiting_for_your_action'),
                    array('Approved in Group',  'list_approved_group'),
        );
        if( $enableReview ){
            if( $isApprover ){
                $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_review_approver);
            }else{
                $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_review);
            }
        }
        if( !$isPostApprover ){
            $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_no_post_approver);
        }
        if( $enableAssignedToBuddy ){
            $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_assigned_to_buddy);
        }
        if( $isApprover ){
            $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_admin);
        }else{
            if( !$enableReview ){
                $itemToUrlMap = array_merge($itemToUrlMap, $itemToUrl_no_admin);
            }
        }

        // override existing settings if review only
        if( $reviewOnly ){
            $itemToUrlMap = $itemToUrl_review_approver;
        }

        // override existing settings if readonly (customer care)
        if( $readOnly ){
            $itemToUrlMap = $itemToUrl_readonly_viewer;
        }

        return $this->left_submenu($breadcrumb, $activeTab, $itemToUrlMap, $countsPerState);
    }
}
