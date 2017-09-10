<?php
namespace CSVImport\Mapping;

use Zend\View\Renderer\PhpRenderer;

class ResourceMapping extends AbstractMapping
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $map;

    public static function getLabel()
    {
        return "Resource data"; // @translate
    }

    public static function getName()
    {
        return 'resource-data';
    }
    public static function getSidebar(PhpRenderer $view)
    {
        return $view->resourceSidebar();
    }

    public function processRow(array $row)
    {
        $this->data = [];

        // First, pull in the global settings.
        $this->processGlobalArgs();

        $multivalueMap = isset($this->args['column-multivalue'])
            ? array_keys($this->args['column-multivalue'])
            : [];
        $multivalueSeparator = $this->args['multivalue-separator'];
        foreach ($row as $index => $values) {
            // Maybe weird, but just assuming a split for ids for simplicity's
            // sake since a list of ids shouldn't have any weird separators.
            $values = explode($multivalueSeparator, $values);
            $values = array_map('trim', $values);
            $this->processCell($index, $values);
        }

        return $this->data;
    }

    protected function processGlobalArgs()
    {
        $data = &$this->data;

        if (!empty($this->args['o:resource_template'])) {
            $resourceTemplate = $this->args['o:resource_template']['o:id'];
            $data['o:resource_template'] = ['o:id' => $resourceTemplate];
        }
        $this->map['resourceTemplate'] = isset($this->args['column-resourcetemplate'])
            ? array_keys($this->args['column-resourcetemplate'])
            : [];

        if (!empty($this->args['o:resource_class'])) {
            $resourceClass = $this->args['o:resource_class']['o:id'];
            $data['o:resource_class'] = ['o:id' => $resourceClass];
        }
        $this->map['resourceClass'] = isset($this->args['column-resourceclass'])
            ? array_keys($this->args['column-resourceclass'])
            : [];

        if (!empty($this->args['o:owner'])) {
            $ownerId = $this->args['o:owner'];
            $data['o:owner'] = ['o:id' => $ownerId];
        }
        $this->map['ownerEmail'] = isset($this->args['column-owneremail'])
            ? array_keys($this->args['column-owneremail'])
            : [];
    }

    protected function processCell($index, array $values)
    {
        $data = &$this->data;

        if (in_array($index, $this->map['resourceTemplate'])) {
            $resourceTemplate = $this->findResourceTemplate($values[0]);
            if ($resourceTemplate) {
                $data['o:resource_template'] = ['o:id' => $resourceTemplate->id()];
            }
        }

        if (in_array($index, $this->map['resourceClass'])) {
            $resourceClass = $this->findResourceClass($values[0]);
            if ($resourceClass) {
                $data['o:resource_class'] = ['o:id' => $resourceClass->id()];
            }
        }

        if (in_array($index, $this->map['ownerEmail'])) {
            $user = $this->findUser($values[0]);
            if ($user) {
                $data['o:owner'] = ['o:id' => $user->id()];
            }
        }
    }

    protected function findResourceTemplate($label)
    {
        $response = $this->api->search('resource_templates', ['label' => $label]);
        $content = $response->getContent();
        if (empty($content)) {
            $this->logger->err(sprintf('"%s" is not a valid Resource Template name.', $label)); // @translate
            $this->setHasErr(true);
            return false;
        }
        $template = $content[0];
        $templateLabel = $template->label();
        if ($label != $templateLabel) {
            $this->logger->err(sprintf('"%s" is not a valid Resource Template name.', $label)); // @translate
            $this->setHasErr(true);
            return false;
        }
        return $content[0];
    }

    protected function findResourceClass($term)
    {
        $response = $this->api->search('resource_classes', ['term' => $term]);
        $content = $response->getContent();
        if (empty($content)) {
            $this->logger->err(sprintf('"%s" is not a valid resource class.', $term) // @translate
                . ' ' . 'Resource Classes must be a Class found on the Vocabularies page.'); // @translate
            $this->setHasErr(true);
            return false;
        }
        $class = $content[0];
        $classTerm = $class->term();
        if ($term != $classTerm) {
            $this->logger->err(sprintf('"%s" is not a valid resource class.', $term) // @translate
                . ' ' . 'Resource Classes must be a Class found on the Vocabularies page.'); // @translate
            $this->setHasErr(true);
            return false;
        }
        return $content[0];
    }

    protected function findUser($email)
    {
        $response = $this->api->search('users', ['email' => $email]);
        $content = $response->getContent();
        if (empty($content)) {
            $this->logger->err(sprintf('"%s" is not a valid user email address.', $email)); // @translate
            $this->setHasErr(true);
            return false;
        }
        $user = $content[0];
        $userEmail = $user->email();
        if ($email != $userEmail) {
            $this->logger->err(sprintf('"%s" is not a valid user email address.', $email)); // @translate
            $this->setHasErr(true);
            return false;
        }
        return $content[0];
    }
}
