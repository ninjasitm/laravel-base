<?php
/**
 * PageConfig Repository
 */
namespace Nitm\Content\Repositories;

use Nitm\Content\Models\PageConfig;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class PageConfigRepository
 *
 * @package App\Repositories
 * @version December 5, 2020, 10:41 pm UTC
 */

class PageConfigRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return PageConfig::class;
    }

    /**
     * Get Config
     *
     * @param  mixed $id
     * @return void
     */
    public function getConfig($id = null)
    {
        return $this->makeModel()->getConfig($id);
    }

    /**
     * Get Page
     *
     * @param  mixed $id
     * @return void
     */
    public function getPage($id)
    {
        return $this->makeModel()->getPage($id);
    }
}
