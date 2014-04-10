<?php

use Grido\Components\Filters\Filter,
    Nette\Utils\Html;

/**
 * Doctrine example.
 * @link http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html
 *
 * @package     Grido
 * @author      Petr Bugyík
 */
final class DoctrinePresenter extends BasePresenter
{
    protected function createComponentGrid($name)
    {
        $grid = new Grido\Grid($this, $name);

        $repository = $this->context->doctrine->entityManager->getRepository('Entities\User');
        $model = new \Grido\DataSources\Doctrine(
            $repository->createQueryBuilder('a') // We need to create query builder with inner join.
                ->addSelect('c')                 // This will produce less SQL queries with prefetch.
                ->innerJoin('a.country', 'c'),
            array('country' => 'c.title'));      // Map country column to the title of the Country entity
        $grid->model = $model;

        $grid->addColumnText('firstname', 'Firstname')
            ->setFilterText()
                ->setSuggestion();

        $grid->addColumnText('surname', 'Surname')
            ->setSortable()
            ->setFilterText()
                ->setSuggestion();

        $grid->addColumnText('gender', 'Gender')
            ->setSortable()
            ->cellPrototype->class[] = 'center';

        $grid->addColumnDate('birthday', 'Birthday', Grido\Components\Columns\Date::FORMAT_TEXT)
            ->setSortable()
            ->setFilterDate()
                ->setCondition($this->gridBirthdayFilterCondition);
        $grid->getColumn('birthday')->cellPrototype->class[] = 'center';

        $baseUri = $this->template->baseUri;
        $grid->addColumnText('country', 'Country')
            ->setSortable()
            ->setCustomRender(function($item) use($baseUri) {
                $img = Html::el('img')->src("$baseUri/img/flags/$item->country_code.gif");
                return "$img $item->country";
            })
            ->setFilterText()
                ->setSuggestion();

        $grid->addColumnText('card', 'Card')
            ->setSortable()
            ->setColumn('cctype') //name of db column
            ->setReplacement(array('MasterCard' => Html::el('b')->setText('MasterCard')))
            ->cellPrototype->class[] = 'center';

        $grid->addColumnEmail('emailaddress', 'Email')
            ->setSortable()
            ->setFilterText();
        $grid->getColumn('emailaddress')->cellPrototype->class[] = 'center';

        $grid->addColumnText('centimeters', 'Height')
            ->setSortable()
            ->setFilterNumber();
        $grid->getColumn('centimeters')->cellPrototype->class[] = 'center';

        $grid->addFilterSelect('gender', 'Gender', array(
            '' => '',
            'female' => 'female',
            'male' => 'male'
        ));

        $grid->addFilterSelect('card', 'Card', array(
                '' => '',
                'MasterCard' => 'MasterCard',
                'Visa' => 'Visa'
            ))
            ->setColumn('cctype');

        $grid->addFilterCheck('preferred', 'Only preferred girls :)')
            ->setCondition(array(
                TRUE => array(array('gender', 'AND', 'centimeters'), array('= ?', '>= ?'), array('female', 170)))
        );

        $grid->addActionHref('edit', 'Edit')
            ->setIcon('pencil');

        $grid->addActionHref('delete', 'Delete')
            ->setIcon('trash')
            ->setConfirm(function($item) {
                return "Are you sure you want to delete {$item->firstname} {$item->surname}?";
        });

        $operation = array('print' => 'Print', 'delete' => 'Delete');
        $grid->setOperation($operation, $this->gridOperationsHandler)
            ->setConfirm('delete', 'Are you sure you want to delete %i items?');

        $grid->filterRenderType = $this->filterRenderType;
        $grid->setExport();
    }
}
