<?php namespace SkyOJ\File;


/**
 * ### Directory Format
 * 
 * ```
 * base     /cont               // put problem descript
 *          /attach             // put static attachments
 *          /testdata/data/
 *          /testdata/make/
 *          /testdata/checker/
 *          judge.json          // judge setting
 * ```
 */


 /**
  * Data Structure to store a problem.
  * Builds directory structure of a problem.
  * It seems that it only handles test data files.
  */
class ProblemDataManager extends ManagerBase
{
    #predefine some file name
    const PROBLEM_JSON_FILE = 'prob.json';
    const CONT_DIR = 'cont/';
    const CONT_ROW_FILE = 'cont/cont.row';
    const CONT_HTML_FILE = 'cont/cont.html';
    const CONT_PDF_FILE = 'cont/cont.pdf';
    const ATTACH_DIR = 'attach/';
    const TESTDATA_DIR = 'testdata/data/';
    const FILENAME_PATTEN = '/^[a-zA-Z0-9\.]{1,64}$/';

    const INPUT_EXT  = "in";
    const OUTPUT_EXT = "ans";
    private $pid;

    public function __construct(int $id,bool $builddir = false)
    {
        $this->pid = $id;
        $this->subrootname = 'problem/'.Path::id2folder($id);
        if( !file_exists($this->base()) )
        {
            if( !$builddir )
                throw new ProblemDataManagerException('No Such Problem!');
            if( !$this->buildStructure() )
                throw new ProblemDataManagerException('buildStructure fail');
        }
    }

    /**
     * Build dirs to store problem assets
     * @return bool
     *  Success or not
     */
    public function buildStructure():bool
    {
        $res = true;
        $res &= $this->mkdir('cont');
        $res &= $this->mkdir('attach');
        $res &= $this->mkdir('testdata/data');
        $res &= $this->mkdir('testdata/make');
        $res &= $this->mkdir('testdata/checker');
        return $res;
    }

    /**
     * Check whether $name is a valid file name
     * @param any $name
     *  Name of dir
     * @return bool
     *  Is valid name
     */
    public function checkFilename($name):bool
    {
        if( !is_string($name) ) return false;
        return preg_match(self::FILENAME_PATTEN,$name);
    }

    /**
     * Get all attachments
     * @return array
     *  array of filenames of attachments
     */
    public function getAttachFiles():array
    {
        return glob($this->base().self::ATTACH_DIR.'*');
    }

    /**
     * Get all test cases files.
     * Ignore invalid test files (e.g. only input or output) if $require_valid_files is true
     * @param bool $require_valid_files
     *  Whether to ignore invalid test files
     * @return array
     *  Return array of test data filenames
     */
    public function getTestdataFiles(bool $require_valid_files = false):array
    {
        $files = glob($this->base().self::TESTDATA_DIR.'*');
        $testcases = [];
        $lastfilename = '';
        $lastfileext  = '';

        foreach( $files as $filename )
        {
            if( !is_file($filename) )
                continue;
            
            if( $require_valid_files )
            {
                $name = pathinfo($filename,PATHINFO_FILENAME);
                $ext  = pathinfo($filename,PATHINFO_EXTENSION);
                if( $ext !== self::INPUT_EXT && $ext !== self::OUTPUT_EXT ) 
                    continue;

                if( $ext === self::INPUT_EXT )
                {
                    // glob will sort all data in alphabetical order, so that .ans will appear first than .in
                    // we can use this ensure all case has an output
                    if( $lastfilename !== $name || $lastfileext !== self::OUTPUT_EXT )
                        continue;
                }
                else
                { 
                    if( $lastfileext === self::OUTPUT_EXT )
                        array_pop($testcases);
                }
                $lastfilename = $name;
                $lastfileext = $ext;
            }
            $testcases[] = $filename;
        }

        if( $require_valid_files && $lastfileext === self::OUTPUT_EXT )
            array_pop($testcases);

        return $testcases;
    }

    /**
     * Extract and copy test files (*.in, *.ans) into testdata/data/
     * @param string $filepath
     *  Path to zip file that contains the testdata files
     * @param bool $cover
     *  Useless parameter
     * @return array
     *  Useless return
     */
    public function copyTestcasesZip(string $filepath, bool $cover = true):array
    {
        if( !class_exists('\\ZipArchive') )
            throw new ProblemDataManagerException('php ZipArchive not enabled!');
        $zip = new \ZipArchive;
  
        if( $zip->open($filepath) === false )
            throw new ProblemDataManagerException('Not a zip file!');

        $tmpdir = tempnam( sys_get_temp_dir() , 'CAS' );

        if( file_exists($tmpdir) )
            unlink($tmpdir);
        mkdir($tmpdir);
 
        $zip->extractTo($tmpdir);
        $zip->close();
        $files = glob($tmpdir.'/*');

        foreach($files as $file)
        {
            $info = pathinfo($file);
            if( $info['extension']==self::INPUT_EXT || $info['extension']==self::OUTPUT_EXT )
            {
                $this->copyin($file,self::TESTDATA_DIR.$info['basename']);
            }
        }

        return [];
    }

    /**
     * Delete test data files
     * @return null
     */
    public function cleanTestdata()
    {
        $files = glob($this->base().self::TESTDATA_DIR.'*');
        foreach($files as $file)
        {
            unlink($file);
        }
    }
}

class ProblemDataManagerException extends \Exception {} 