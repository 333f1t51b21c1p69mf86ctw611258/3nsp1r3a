#! /bin/bash
redis-cli -h redis del App2_workflow_notifier_at_approve 
redis-cli -h redis lpush App2_workflow_notifier_at_approve enspirea.dev@gmail.com
redis-cli -h redis lpush App2_workflow_notifier_at_approve enspirea.dev@gmail.com
redis-cli -h redis lpush App2_workflow_notifier_at_approve enspirea.dev@gmail.com
