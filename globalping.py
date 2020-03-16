'''

[jaist]
Japan
JAIST
http://ftp.jaist.ac.jp/pub/Linux/ubuntu/

[neowiz]
Korea
NeoWiz
http://ftp.neowiz.com/ubuntu/

[devcloud]
China
DevCloud
http://mirrors.huaweicloud.com/repository/ubuntu/

[ayong]
Singapore
Andrew Yong
http://mirror.0x.sg/ubuntu/

[ucdavis]
USA (West)
University of California at Davis
http://mirror.math.ucdavis.edu/ubuntu/

[umd]
USA (East)
University of Maryland
http://mirror.umd.edu/ubuntu/

[oxford]
Europe
IT Services, University of Oxford
http://mirror.ox.ac.uk/sites/archive.ubuntu.com/ubuntu/

[melbournit]
Austraila
Melbourne IT
http://ubuntu.melbourneitmirror.net/archive/

[8888]
CDN
8.8.8.8

ping -q -c 1000 
'''

ping_targets={
    "jaist":"ftp.jaist.ac.jp",
    "neowiz":"ftp.neowiz.com",
    "devcloud":"mirrors.huaweicloud.com",
    "ayong":"mirror.0x.sg",
    "ucdavis":"mirror.math.ucdavis.edu",
    "umd":"mirror.umd.edu",
    "oxford":"mirror.ox.ac.uk",
    "melbournit":"ubuntu.melbourneitmirror.net",
    "8888":"8.8.8.8"
    }

import subprocess
import csv
import time
import datetime
import os
import os.path
import sys

class PingResult:
    def __init__(self,*, timestamp, hostname, ip, total, received, lossrate, rtt_min, rtt_avg, rtt_max, rtt_mdv):
        self._timestamp=timestamp
        self._hostname=hostname
        self._ip=ip
        self._total=total
        self._received=received
        self._lossrate=lossrate
        self._rtt_min=rtt_min
        self._rtt_avg=rtt_avg
        self._rtt_max=rtt_max
        self._rtt_mdv=rtt_mdv
    @property
    def timestamp(self):
        return self._timestamp
    @property
    def hostname(self):
        return self._hostname
    @property
    def ip(self):
        return self._ip
    @property
    def total(self):
        return self._total
    @property
    def received(self):
        return self._received
    @property
    def failed(self):
        return self.total-self.received
    @property
    def lossrate(self):
        return self.failed/self.total
    @property
    def rtt_avg(self):
        return self._rtt_avg
    @property
    def rtt_mdev(self):
        return self._rtt_mdv
    @property
    def rtt_min(self):
        return self._rtt_min
    @property
    def rtt_max(self):
        return self._rtt_max

    def __str__(self):
        s=''
        s+="Ping to {} ({})\n".format(self.hostname, self.ip)
        s+="  {}/{} ({:.2f}% loss)\n".format(self.received,self.total,self.lossrate*100)
        s+="  mRTT {:.1f}ms\n".format(self.rtt_avg)
        s+="  Measured on {}".format(datetime.datetime.fromtimestamp(self.timestamp).strftime("%Y-%m-%d %H:%M:%S"))
        return s

    @classmethod
    def csv_header(cls):
        return (
            "timestamp",
            "hostname",
            "ip",
            "total",
            "received",
            "lossrate",
            "rtt_avg",
            "rtt_min",
            "rtt_max",
            "rtt_mdev")
    def as_csv_row(self):
        return (
            self.timestamp,
            self.hostname,
            self.ip,
            self.total,
            self.received,
            self.lossrate,
            self.rtt_avg,
            self.rtt_min,
            self.rtt_max,
            self.rtt_mdev)

    @classmethod
    def from_console_output(cls,b,timestamp):
        outs=b.decode("utf-8")
        
        hname=outs.split("PING")[1].split("(")[0].strip()

        ip=outs.split("(")[1].split(")")[0]
        
        s=outs.split("---")[-1].strip()
        ss=s.split("\n")
        stats=ss[0].strip().split(",")
        rtts=ss[1].split("=")[1].replace("ms","").strip()
        for stat in stats:
            stat_stripped=stat.strip()
            stat_val=stat_stripped.split(" ")[0]
            
            if stat_stripped.endswith("packets transmitted"):
                total=int(stat_val)
            if stat_stripped.endswith("received"):
                received=int(stat_val)
            if stat_stripped.endswith("packet loss"):
                lossrate=float(stat_val.replace("%",""))/100
        
        rttss=rtts.split("/")
        RTT_min=float(rttss[0])
        RTT_avg=float(rttss[1])
        RTT_max=float(rttss[2])
        RTT_mdv=float(rttss[3])

        
        return PingResult(
            timestamp=timestamp,
            hostname=hname,
            ip=ip,
            total=total,
            received=received,
            lossrate=lossrate,
            rtt_min=RTT_min,
            rtt_avg=RTT_avg,
            rtt_max=RTT_max,
            rtt_mdv=RTT_mdv
            )

    
def ping(host, count=3):
    #out=subprocess.check_output("ping -q -c {} {}".format(count,host))
    p=subprocess.Popen(["ping","-q","-c",str(count),host],
                       stdout=subprocess.PIPE,
                       stderr=subprocess.PIPE)
    cout,cerr=p.communicate()
    return PingResult.from_console_output(cout,time.time())

def pingall(targets, count=1000, interval=1):
    proc=dict()
    rres=dict()
    res=dict()

    starttime=time.time()
    for tname in targets:
        host=targets[tname]
        proc[tname]=subprocess.Popen(
                            ["ping",
                             "-q",
                             "-c",str(count),
                             "-i",str(interval),
                             host],
                            stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE)
        
    for tname in targets:
        rres[tname]=proc[tname].communicate()[0]

    for tname in targets:
        try:
            res[tname]=PingResult.from_console_output(rres[tname],starttime)
        except:
            print("Error while parsing",tname)
            print("output:",rres[tname])

    return res

def test():
    print(ping("8.8.8.8"))
    
def main():
    if "--count" in sys.argv:
        count=int(sys.argv[sys.argv.index("--count")+1])
    else:
        count=3
    print("Pinging...")
    print("count =",count)
    r=pingall(
        ping_targets,
        count=count,
        interval=1)
    for i in r:
        #print(i)
        #print(r[i])
        #print()
        pass

    print("Writing data to CSV...")
    if not os.path.isdir("data"):
        os.mkdir("data")
    for i in r:
        filepath="data/"+i+".csv"
        header_required=not os.path.isfile(filepath)
            
        with open(filepath, "a", newline='') as f:
            writer=csv.writer(f)
            if header_required:
                writer.writerow(PingResult.csv_header())
            writer.writerow(r[i].as_csv_row())
    print("Written.")

    print("Writing abridged CSV...")
    if not os.path.isdir("adata"):
        os.mkdir("adata")
    for i in r:
        filepath="adata/"+i+".csv"
        origfilepath="data/"+i+".csv"
        ll=subprocess.check_output(["tail", "-n", "100", origfilepath]).decode("utf-8")
        fl=subprocess.check_output(["head", "-n", "1", origfilepath]).decode("utf-8")

        if ll.startswith(fl):
            res=ll
        else:
            res=fl+ll
        with open(filepath,"w") as f:
            f.write(res)

    print("Done!")

if __name__=="__main__":
    main()
