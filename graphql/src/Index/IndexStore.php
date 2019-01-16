<?php

namespace App\Index;

use Doctrine\DBAL\Connection;
use PDO;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Index persistence service.
 */
class IndexStore
{
    /** @var Connection Database connection instance. */
    private $db;

    /**
     * Initializes the service, injecting required dependencies.
     *
     * @param   Connection      $db     Database connection instance
     * @return  void
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Pushes a new index in the store.
     *
     * @param   array           $respositories
     * @return  void
     */
    public function putRepositoriesMetadata(array $repositories)
    {
        // Load already existing ids.
        $existingIds = $this->db
            ->executeQuery('SELECT id FROM repositories')
            ->fetchAll(PDO::FETCH_COLUMN);

        // Load new ids.
        $newIds = array_column($repositories, 'id');

        // Delete old records.
        foreach ($existingIds as $id) {
            if (!in_array($id, $newIds)) {
                $this->db->delete('repositories', [ 'id' => $id ]);
            }
        }

        // Prepare extra data
        $updated = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        // Iterate provided repositories.
        foreach ($repositories as $repository) {
            if (in_array($repository['id'], $existingIds)) {
                // Update existing row
                $this->db->update(
                    'repositories',
                    [
                        // Update basic data
                        'name'          => $repository['name'],
                        'url'           => $repository['url'],
                        'description'   => $repository['description'],

                        // Update metadata
                        'gh_metadata'               => json_encode($repository),
                        'gh_metadata_fetched_at'    => $updated,
                    ],
                    [ 'id' => $repository['id'] ]
                );
            } else {
                // Create new row
                $this->db->insert(
                    'repositories',
                    [
                        // Insert basic data
                        'id'            => $repository['id'],
                        'name'          => $repository['name'],
                        'url'           => $repository['url'],
                        'description'   => $repository['description'],

                        // Update metadata
                        'gh_metadata'               => json_encode($repository),
                        'gh_metadata_fetched_at'    => $updated,
                    ]
                );
            }
        }
    }

    /**
     * Returns all indexed repositories.
     *
     * @return  array
     */
    public function getRepositories()
    {
        return $this->db->executeQuery('select * from repositories')->fetchAll();
    }

    /**
     * Marks a repository as cloned.
     *
     * @param   string              $id
     * @param   string              $relativePath
     * @param   DateTimeInterface   $when
     * @return  void
     */
    public function markGitFetched($id, $relativePath, DateTimeInterface $when)
    {
        $this->db->update(
            'repositories',
            [
                'git_path'          => $relativePath,
                'git_fetched_at'    => $when->format('Y-m-d H:i:s'),
            ],
            [
                'id'                => $id,
            ]
        );
    }

    /**
     * Unmarks a repository as cloned.
     *
     * @param   string              $id
     * @return  void
     */
    public function unmarkGitFetched($id)
    {
        $this->db->update(
            'repositories',
            [
                'git_path'          => null,
                'git_fetched_at'    => null,
            ],
            [
                'id'                => $id,
            ]
        );
    }
}
