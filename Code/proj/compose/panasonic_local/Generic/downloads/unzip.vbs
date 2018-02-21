Set objArgs = WScript.Arguments
OutputFolder = objArgs(0)
ZipFile = objArgs(1)

Set oApp = CreateObject("Shell.Application")
oApp.Namespace(OutputFolder).CopyHere oApp.Namespace(ZipFile).Items

On Error Resume Next
Set FSO = CreateObject("scripting.filesystemobject")
FSO.deletefolder Environ("Temp") & "\Temporary Directory*", True

Set oApp = Nothing
Set FSO = Nothing
