<?php
// This file was auto-generated from sdk-root/src/data/batch/2016-08-10/examples-1.json
return [ 'version' => '1.0', 'examples' => [ 'CancelJob' => [ [ 'input' => [ 'jobId' => '1d828f65-7a4d-42e8-996d-3b900ed59dc4', 'reason' => 'Cancelling job.', ], 'output' => [], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example cancels a job with the specified job ID.', 'id' => 'to-cancel-a-job-1481152314733', 'title' => 'To cancel a job', ], ], 'CreateComputeEnvironment' => [ [ 'input' => [ 'type' => 'MANAGED', 'computeEnvironmentName' => 'C4OnDemand', 'computeResources' => [ 'type' => 'EC2', 'desiredvCpus' => 48, 'ec2KeyPair' => 'id_rsa', 'instanceRole' => 'ecsInstanceRole', 'instanceTypes' => [ 'c4.large', 'c4.xlarge', 'c4.2xlarge', 'c4.4xlarge', 'c4.8xlarge', ], 'maxvCpus' => 128, 'minvCpus' => 0, 'securityGroupIds' => [ 'sg-cf5093b2', ], 'subnets' => [ 'subnet-220c0e0a', 'subnet-1a95556d', 'subnet-978f6dce', ], 'tags' => [ 'Name' => 'Batch Instance - C4OnDemand', ], ], 'serviceRole' => 'arn:aws:iam::012345678910:role/AWSBatchServiceRole', 'state' => 'ENABLED', ], 'output' => [ 'computeEnvironmentArn' => 'arn:aws:batch:us-east-1:012345678910:compute-environment/C4OnDemand', 'computeEnvironmentName' => 'C4OnDemand', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example creates a managed compute environment with specific C4 instance types that are launched on demand. The compute environment is called C4OnDemand.', 'id' => 'to-create-a-managed-ec2-compute-environment-1481152600017', 'title' => 'To create a managed EC2 compute environment', ], [ 'input' => [ 'type' => 'MANAGED', 'computeEnvironmentName' => 'M4Spot', 'computeResources' => [ 'type' => 'SPOT', 'bidPercentage' => 20, 'desiredvCpus' => 4, 'ec2KeyPair' => 'id_rsa', 'instanceRole' => 'ecsInstanceRole', 'instanceTypes' => [ 'm4', ], 'maxvCpus' => 128, 'minvCpus' => 0, 'securityGroupIds' => [ 'sg-cf5093b2', ], 'spotIamFleetRole' => 'arn:aws:iam::012345678910:role/aws-ec2-spot-fleet-role', 'subnets' => [ 'subnet-220c0e0a', 'subnet-1a95556d', 'subnet-978f6dce', ], 'tags' => [ 'Name' => 'Batch Instance - M4Spot', ], ], 'serviceRole' => 'arn:aws:iam::012345678910:role/AWSBatchServiceRole', 'state' => 'ENABLED', ], 'output' => [ 'computeEnvironmentArn' => 'arn:aws:batch:us-east-1:012345678910:compute-environment/M4Spot', 'computeEnvironmentName' => 'M4Spot', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example creates a managed compute environment with the M4 instance type that is launched when the Spot bid price is at or below 20% of the On-Demand price for the instance type. The compute environment is called M4Spot.', 'id' => 'to-create-a-managed-ec2-spot-compute-environment-1481152844190', 'title' => 'To create a managed EC2 Spot compute environment', ], ], 'CreateJobQueue' => [ [ 'input' => [ 'computeEnvironmentOrder' => [ [ 'computeEnvironment' => 'M4Spot', 'order' => 1, ], ], 'jobQueueName' => 'LowPriority', 'priority' => 1, 'state' => 'ENABLED', ], 'output' => [ 'jobQueueArn' => 'arn:aws:batch:us-east-1:012345678910:job-queue/LowPriority', 'jobQueueName' => 'LowPriority', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example creates a job queue called LowPriority that uses the M4Spot compute environment.', 'id' => 'to-create-a-job-queue-with-a-single-compute-environment-1481152967946', 'title' => 'To create a job queue with a single compute environment', ], [ 'input' => [ 'computeEnvironmentOrder' => [ [ 'computeEnvironment' => 'C4OnDemand', 'order' => 1, ], [ 'computeEnvironment' => 'M4Spot', 'order' => 2, ], ], 'jobQueueName' => 'HighPriority', 'priority' => 10, 'state' => 'ENABLED', ], 'output' => [ 'jobQueueArn' => 'arn:aws:batch:us-east-1:012345678910:job-queue/HighPriority', 'jobQueueName' => 'HighPriority', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example creates a job queue called HighPriority that uses the C4OnDemand compute environment with an order of 1 and the M4Spot compute environment with an order of 2.', 'id' => 'to-create-a-job-queue-with-multiple-compute-environments-1481153027051', 'title' => 'To create a job queue with multiple compute environments', ], ], 'DeleteComputeEnvironment' => [ [ 'input' => [ 'computeEnvironment' => 'P2OnDemand', ], 'output' => [], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example deletes the P2OnDemand compute environment.', 'id' => 'to-delete-a-compute-environment-1481153105644', 'title' => 'To delete a compute environment', ], ], 'DeleteJobQueue' => [ [ 'input' => [ 'jobQueue' => 'GPGPU', ], 'output' => [], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example deletes the GPGPU job queue.', 'id' => 'to-delete-a-job-queue-1481153508134', 'title' => 'To delete a job queue', ], ], 'DeregisterJobDefinition' => [ [ 'input' => [ 'jobDefinition' => 'sleep10', ], 'output' => [], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example deregisters a job definition called sleep10.', 'id' => 'to-deregister-a-job-definition-1481153579565', 'title' => 'To deregister a job definition', ], ], 'DescribeComputeEnvironments' => [ [ 'input' => [ 'computeEnvironments' => [ 'P2OnDemand', ], ], 'output' => [ 'computeEnvironments' => [ [ 'type' => 'MANAGED', 'computeEnvironmentArn' => 'arn:aws:batch:us-east-1:012345678910:compute-environment/P2OnDemand', 'computeEnvironmentName' => 'P2OnDemand', 'computeResources' => [ 'type' => 'EC2', 'desiredvCpus' => 48, 'ec2KeyPair' => 'id_rsa', 'instanceRole' => 'ecsInstanceRole', 'instanceTypes' => [ 'p2', ], 'maxvCpus' => 128, 'minvCpus' => 0, 'securityGroupIds' => [ 'sg-cf5093b2', ], 'subnets' => [ 'subnet-220c0e0a', 'subnet-1a95556d', 'subnet-978f6dce', ], 'tags' => [ 'Name' => 'Batch Instance - P2OnDemand', ], ], 'ecsClusterArn' => 'arn:aws:ecs:us-east-1:012345678910:cluster/P2OnDemand_Batch_2c06f29d-d1fe-3a49-879d-42394c86effc', 'serviceRole' => 'arn:aws:iam::012345678910:role/AWSBatchServiceRole', 'state' => 'ENABLED', 'status' => 'VALID', 'statusReason' => 'ComputeEnvironment Healthy', ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example describes the P2OnDemand compute environment.', 'id' => 'to-describe-a-compute-environment-1481153713334', 'title' => 'To describe a compute environment', ], ], 'DescribeJobDefinitions' => [ [ 'input' => [ 'status' => 'ACTIVE', ], 'output' => [ 'jobDefinitions' => [ [ 'type' => 'container', 'containerProperties' => [ 'command' => [ 'sleep', '60', ], 'environment' => [], 'image' => 'busybox', 'memory' => 128, 'mountPoints' => [], 'ulimits' => [], 'vcpus' => 1, 'volumes' => [], ], 'jobDefinitionArn' => 'arn:aws:batch:us-east-1:012345678910:job-definition/sleep60:1', 'jobDefinitionName' => 'sleep60', 'revision' => 1, 'status' => 'ACTIVE', ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example describes all of your active job definitions.', 'id' => 'to-describe-active-job-definitions-1481153895831', 'title' => 'To describe active job definitions', ], ], 'DescribeJobQueues' => [ [ 'input' => [ 'jobQueues' => [ 'HighPriority', ], ], 'output' => [ 'jobQueues' => [ [ 'computeEnvironmentOrder' => [ [ 'computeEnvironment' => 'arn:aws:batch:us-east-1:012345678910:compute-environment/C4OnDemand', 'order' => 1, ], ], 'jobQueueArn' => 'arn:aws:batch:us-east-1:012345678910:job-queue/HighPriority', 'jobQueueName' => 'HighPriority', 'priority' => 1, 'state' => 'ENABLED', 'status' => 'VALID', 'statusReason' => 'JobQueue Healthy', ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example describes the HighPriority job queue.', 'id' => 'to-describe-a-job-queue-1481153995804', 'title' => 'To describe a job queue', ], ], 'DescribeJobs' => [ [ 'input' => [ 'jobs' => [ '24fa2d7a-64c4-49d2-8b47-f8da4fbde8e9', ], ], 'output' => [ 'jobs' => [ [ 'container' => [ 'command' => [ 'sleep', '60', ], 'containerInstanceArn' => 'arn:aws:ecs:us-east-1:012345678910:container-instance/5406d7cd-58bd-4b8f-9936-48d7c6b1526c', 'environment' => [], 'exitCode' => 0, 'image' => 'busybox', 'memory' => 128, 'mountPoints' => [], 'ulimits' => [], 'vcpus' => 1, 'volumes' => [], ], 'createdAt' => 1480460782010, 'dependsOn' => [], 'jobDefinition' => 'sleep60', 'jobId' => '24fa2d7a-64c4-49d2-8b47-f8da4fbde8e9', 'jobName' => 'example', 'jobQueue' => 'arn:aws:batch:us-east-1:012345678910:job-queue/HighPriority', 'parameters' => [], 'startedAt' => 1480460816500, 'status' => 'SUCCEEDED', 'stoppedAt' => 1480460880699, ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example describes a job with the specified job ID.', 'id' => 'to-describe-a-specific-job-1481154090490', 'title' => 'To describe a specific job', ], ], 'ListJobs' => [ [ 'input' => [ 'jobQueue' => 'HighPriority', ], 'output' => [ 'jobSummaryList' => [ [ 'jobId' => 'e66ff5fd-a1ff-4640-b1a2-0b0a142f49bb', 'jobName' => 'example', ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example lists the running jobs in the HighPriority job queue.', 'id' => 'to-list-running-jobs-1481154202164', 'title' => 'To list running jobs', ], [ 'input' => [ 'jobQueue' => 'HighPriority', 'jobStatus' => 'SUBMITTED', ], 'output' => [ 'jobSummaryList' => [ [ 'jobId' => '68f0c163-fbd4-44e6-9fd1-25b14a434786', 'jobName' => 'example', ], ], ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example lists jobs in the HighPriority job queue that are in the SUBMITTED job status.', 'id' => 'to-list-submitted-jobs-1481154251623', 'title' => 'To list submitted jobs', ], ], 'RegisterJobDefinition' => [ [ 'input' => [ 'type' => 'container', 'containerProperties' => [ 'command' => [ 'sleep', '10', ], 'image' => 'busybox', 'memory' => 128, 'vcpus' => 1, ], 'jobDefinitionName' => 'sleep10', ], 'output' => [ 'jobDefinitionArn' => 'arn:aws:batch:us-east-1:012345678910:job-definition/sleep10:1', 'jobDefinitionName' => 'sleep10', 'revision' => 1, ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example registers a job definition for a simple container job.', 'id' => 'to-register-a-job-definition-1481154325325', 'title' => 'To register a job definition', ], ], 'SubmitJob' => [ [ 'input' => [ 'jobDefinition' => 'sleep60', 'jobName' => 'example', 'jobQueue' => 'HighPriority', ], 'output' => [ 'jobId' => '876da822-4198-45f2-a252-6cea32512ea8', 'jobName' => 'example', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example submits a simple container job called example to the HighPriority job queue.', 'id' => 'to-submit-a-job-to-a-queue-1481154481673', 'title' => 'To submit a job to a queue', ], ], 'TerminateJob' => [ [ 'input' => [ 'jobId' => '61e743ed-35e4-48da-b2de-5c8333821c84', 'reason' => 'Terminating job.', ], 'output' => [], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example terminates a job with the specified job ID.', 'id' => 'to-terminate-a-job-1481154558276', 'title' => 'To terminate a job', ], ], 'UpdateComputeEnvironment' => [ [ 'input' => [ 'computeEnvironment' => 'P2OnDemand', 'state' => 'DISABLED', ], 'output' => [ 'computeEnvironmentArn' => 'arn:aws:batch:us-east-1:012345678910:compute-environment/P2OnDemand', 'computeEnvironmentName' => 'P2OnDemand', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example disables the P2OnDemand compute environment so it can be deleted.', 'id' => 'to-update-a-compute-environment-1481154702731', 'title' => 'To update a compute environment', ], ], 'UpdateJobQueue' => [ [ 'input' => [ 'jobQueue' => 'GPGPU', 'state' => 'DISABLED', ], 'output' => [ 'jobQueueArn' => 'arn:aws:batch:us-east-1:012345678910:job-queue/GPGPU', 'jobQueueName' => 'GPGPU', ], 'comments' => [ 'input' => [], 'output' => [], ], 'description' => 'This example disables a job queue so that it can be deleted.', 'id' => 'to-update-a-job-queue-1481154806981', 'title' => 'To update a job queue', ], ], ],];
