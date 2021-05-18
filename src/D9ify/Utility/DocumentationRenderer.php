<?php


namespace D9ify\Utility;

/**
 * Class DocumentationRenderer
 *
 * @package D9ify\Utility
 */
class DocumentationRenderer
{

    /**
     * @var \D9ify\Utility\DocBlock
     */
    protected DocBlock $block;

    /**
     * DocumentationRenderer constructor.
     *
     * @param \D9ify\Utility\DocBlock $block
     */
    public function __construct(DocBlock $block)
    {
        $this->block = $block;
    }

    /**
     * @return array|null
     */
    public function serialize()
    {
        if (isset($this->block->step)) {
            return [
                "### {$this->block->step[0]['number']}" .
                PHP_EOL . PHP_EOL .
                $this->getDescription() . PHP_EOL,
                PHP_EOL,
            ];
        }
        if (isset($this->block->name)) {
            return [
                "# {$this->block->name[0]}" . PHP_EOL,
                $this->getDescription(null) . PHP_EOL,
                "## USAGE " . PHP_EOL ,
                "  ```{$this->block->usage[0]}```" . PHP_EOL ,
                "## STEPS" . PHP_EOL . PHP_EOL
            ];
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return join(PHP_EOL, $this->serialize());
    }


    public function getDescription($delimiter = " ")
    {

        if (isset($this->block->description)) {
            $lines = [];
            foreach ($this->block->description as $descriptionTag) {
                $lines += explode(PHP_EOL, $descriptionTag);
            }
            return join(
                PHP_EOL,
                array_map(function ($item) use ($delimiter) {
                        return $delimiter . $item;
                }, $lines)
            );
        }
    }
}
