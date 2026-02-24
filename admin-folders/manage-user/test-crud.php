<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');

$URL = 'https://pdqhbxtxvxrwtkvymjlm.supabase.co';
$KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U';
$TBL = 'Users';

function sb($m,$ep,$b=null){
    global $URL,$KEY;
    $ch=curl_init($URL.'/rest/v1/'.$ep);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CUSTOMREQUEST=>$m,
        CURLOPT_HTTPHEADER=>[
            'Content-Type: application/json',
            'apikey: '.$KEY,
            'Authorization: Bearer '.$KEY,
            'Prefer: return=representation',
        ],
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_TIMEOUT=>15,
    ]);
    if($b) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($b));
    $r=curl_exec($ch);
    $s=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $e=curl_error($ch);
    curl_close($ch);
    return['s'=>$s,'b'=>json_decode($r,true),'raw'=>$r,'e'=>$e];
}
?>
<!DOCTYPE html><html><head><title>CRUD Test</title>
<style>
body{font-family:monospace;padding:24px;background:#f5f2ee;max-width:900px;margin:0 auto}
h2{font-family:serif;color:#6b4226}
.box{background:#fff;border-radius:8px;padding:16px 20px;margin:12px 0;border:1px solid #ddd}
.ok{border-left:5px solid #2d9e6b}.fail{border-left:5px solid #e05555}.warn{border-left:5px solid #e67e22}
h3{margin:0 0 8px;font-size:14px}
pre{font-size:12px;color:#333;white-space:pre-wrap;word-break:break-all;background:#f8f8f8;padding:10px;border-radius:4px;margin:4px 0}
.fix{margin-top:8px;font-size:12px;background:#fff8e1;padding:10px;border-radius:4px;border:1px solid #ffe082;line-height:1.6}
.step{background:#e8f4fd;border:1px solid #90caf9;border-radius:6px;padding:12px 16px;margin:4px 0;font-size:12px;line-height:1.8}
</style></head><body>
<h2>Mrs.Alu — Supabase CRUD Test</h2>

<?php
// GET
$r=sb('GET',$TBL.'?select=user_id,first_name,last_name,email&limit=5');
$cls=$r['s']===200?'ok':'fail';
echo "<div class='box $cls'><h3>".($r['s']===200?'✅':'❌')." GET — HTTP {$r['s']}</h3><pre>{$r['raw']}</pre></div>";

// POST
$email='crudtest_'.time().'@del.com';
$r2=sb('POST',$TBL,[
    'first_name'=>'Test',
    'last_name'=>'User',
    'email'=>$email,
    'password_hash'=>'$2y$10$YourHashedPasswordHere123456789012345678901234567',
    'phone'=>'',
    'created_at'=>date('c'),
    'updated_at'=>date('c'),
]);

$id=null;
if(in_array($r2['s'],[200,201])){
    $row=is_array($r2['b'])?($r2['b'][0]??$r2['b']):$r2['b'];
    $id=$row['user_id']??null;
    echo "<div class='box ok'><h3>✅ POST INSERT — HTTP {$r2['s']} — user_id={$id}</h3><pre>".json_encode($row,JSON_PRETTY_PRINT)."</pre></div>";
} else {
    $raw=$r2['raw'];
    $low=strtolower($raw);
    echo "<div class='box fail'><h3>❌ POST INSERT — HTTP {$r2['s']}</h3>
    <pre>$raw</pre>";

    if(str_contains($low,'rls')||str_contains($low,'security policy')||in_array($r2['s'],[401,403])){
        echo "<div class='fix'>
        <strong>RLS is still active.</strong> Follow these exact steps:<br>
        <div class='step'>
        1. Go to <a href='https://supabase.com' target='_blank'>supabase.com</a> → your project<br>
        2. Left sidebar → click <strong>Database</strong> (NOT Authentication)<br>
        3. Click <strong>Tables</strong><br>
        4. Find <strong>Users</strong> row → look for the RLS column on the right<br>
        5. Click the toggle to turn RLS <strong>OFF</strong><br><br>
        <strong>OR run this SQL:</strong><br>
        Go to left sidebar → <strong>SQL Editor</strong> → New Query → paste and Run:<br>
        <code>ALTER TABLE \"Users\" DISABLE ROW LEVEL SECURITY;</code>
        </div></div>";
    } elseif(str_contains($low,'column')||str_contains($low,'schema')||$r2['s']===400){
        echo "<div class='fix'><strong>Column name mismatch.</strong><br>
        Your Supabase Users table must have EXACTLY these columns:<br>
        <code>user_id, first_name, last_name, email, password_hash, phone, created_at, updated_at</code></div>";
    }
    echo "</div>";
}

// PATCH
if($id){
    $r3=sb('PATCH',$TBL.'?user_id=eq.'.urlencode($id),['first_name'=>'Updated','updated_at'=>date('c')]);
    $cls=in_array($r3['s'],[200,204])?'ok':'fail';
    $ic=in_array($r3['s'],[200,204])?'✅':'❌';
    echo "<div class='box $cls'><h3>$ic PATCH UPDATE — HTTP {$r3['s']}</h3><pre>{$r3['raw']}</pre></div>";
} else {
    echo "<div class='box warn'><h3>⚠️ PATCH — Skipped (INSERT failed)</h3></div>";
}

// DELETE
if($id){
    $r4=sb('DELETE',$TBL.'?user_id=eq.'.urlencode($id));
    $cls=in_array($r4['s'],[200,204])?'ok':'fail';
    $ic=in_array($r4['s'],[200,204])?'✅':'❌';
    echo "<div class='box $cls'><h3>$ic DELETE — HTTP {$r4['s']} — Test row cleaned up</h3></div>";
} else {
    echo "<div class='box warn'><h3>⚠️ DELETE — Skipped</h3></div>";
}
?>
</body></html>