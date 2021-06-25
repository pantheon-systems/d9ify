<?php


namespace Pantheon\D9ify\Commands;


use Robo\Contract\TaskInterface;
use Robo\Tasks;

class BuildDockerContainersCommand extends Tasks {


    /**
     * @command buildDockerContainers
     * @example
     * 	docker build \
     *     --build-arg BUILD_DATE="${DATE_TAG}" \
     *     --build-arg VCS_REF="${VCS_REF}" \
     *     --build-arg VERSION="${VERSION}" \
     *     --build-arg REPO_NAME="${REPO_NAME}" \
     *     --tag=$(call getServiceContainer,$*):latest \
     *     ./$*-container
     *
     *
     *
     */
    public function buildDockerContainers() {
        $realpath = realpath(dirname(\Composer\Factory::getComposerFile()));
        $this->stopOnFail(true);
        $now = new \DateTime();
        $vcs_ref = trim(`git describe --tags --always --match="v*"`);
        $version = trim(`git describe --tags --always --dirty --match="v*"`);
        $repo_name = basename($realpath);
        $this->taskDockerBuild('ContainerBuilders/mysql-container')
            ->rawArg('--build-arg BUILD_DATE=' . $now->format(\DateTimeInterface::ISO8601))
            ->rawArg(sprintf('--build-arg VCS_REF="%s"', $vcs_ref))
            ->rawArg(sprintf('--build-arg VERSION="%s"', $version))
            ->rawArg(sprintf('--build-arg REPO_NAME="%s"', $repo_name))
            ->tag('docker.io/stovak/project-demigods-mysql:latest')
            ->run();
        $this->taskDockerBuild('ContainerBuilders/php-container')
            ->rawArg('--build-arg BUILD_DATE=' . $now->format(\DateTimeInterface::ISO8601))
            ->rawArg(sprintf('--build-arg VCS_REF="%s"', $vcs_ref))
            ->rawArg(sprintf('--build-arg VERSION="%s"', $version))
            ->rawArg(sprintf('--build-arg REPO_NAME="%s"', $repo_name))
            ->tag('docker.io/stovak/project-demigods-php:latest')
            ->run();
        $this->taskDockerBuild('ContainerBuilders/solr-container')
            ->rawArg('--build-arg BUILD_DATE=' . $now->format(\DateTimeInterface::ISO8601))
            ->rawArg(sprintf('--build-arg VCS_REF="%s"', $vcs_ref))
            ->rawArg(sprintf('--build-arg VERSION="%s"', $version))
            ->rawArg(sprintf('--build-arg REPO_NAME="%s"', $repo_name))
            ->tag('docker.io/stovak/project-demigods-solr:latest')
            ->run();
        $this->taskDockerBuild('ContainerBuilders/nginx-container')
            ->rawArg('--build-arg BUILD_DATE=' . $now->format(\DateTimeInterface::ISO8601))
            ->rawArg(sprintf('--build-arg VCS_REF="%s"', $vcs_ref))
            ->rawArg(sprintf('--build-arg VERSION="%s"', $version))
            ->rawArg(sprintf('--build-arg REPO_NAME="%s"', $repo_name))
            ->tag('docker.io/stovak/project-demigods-nginx:latest')
            ->run();
        $this->taskDockerBuild('ContainerBuilders/redis-container')
            ->rawArg('--build-arg BUILD_DATE=' . $now->format(\DateTimeInterface::ISO8601))
            ->rawArg(sprintf('--build-arg VCS_REF="%s"', $vcs_ref))
            ->rawArg(sprintf('--build-arg VERSION="%s"', $version))
            ->rawArg(sprintf('--build-arg REPO_NAME="%s"', $repo_name))
            ->tag('docker.io/stovak/project-demigods-redis:latest')
            ->run();
        passthru('docker push docker.io/stovak/project-demigods-mysql:latest');
        passthru('docker push docker.io/stovak/project-demigods-php:latest');
        passthru('docker push docker.io/stovak/project-demigods-solr:latest');
        passthru('docker push docker.io/stovak/project-demigods-nginx:latest');
        passthru('docker push docker.io/stovak/project-demigods-redis:latest');

    }

}
