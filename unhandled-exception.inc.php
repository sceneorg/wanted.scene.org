<?php
set_exception_handler(function($exception)
{
  header("HTTP/1.1 500 Internal Server Error");
  
  echo "
  <html>
    <head>
      <title>There's been an error</title>
      <style>body{background:linear-gradient(180deg,black,#400);font-family:sans-serif;}div{margin:30px auto;max-width:800px;background:#600;border:3px solid black;color:#eee;padding:20px}pre{word-wrap:break-word;white-space:pre-wrap;}</style>
    </head>
    <body>
      <div>
        <h1>There's been an error</h1>
        <pre>".htmlspecialchars($exception)."</pre>
      </div>
    </body>
  </html>
  ";
  
});

?>