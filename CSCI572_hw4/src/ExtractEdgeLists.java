import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.File;
import java.util.*;
import java.io.*;

public class ExtractEdgeLists {

	public static void main(String[] args) throws Exception {
		String mapPath = "/Users/jessiekuo/Desktop/CSCI572_HW4/solr-7.7.0/EdgeLists/URLtoHTML_reuters_news.csv";
        File mapFile = new File(mapPath);
        Map<String,String> fileUrlMap = new HashMap<>();
        Map<String,String> urlFileMap = new HashMap<>();
        PrintWriter writer = new PrintWriter("/Users/jessiekuo/Desktop/CSCI572_HW4/solr-7.7.0/EdgeLists/edgeList.txt");
        try {
            Scanner sc = new Scanner(mapFile);
            while(sc.hasNext()) {
                String[] pairs = sc.next().split(",");
                fileUrlMap.put(pairs[0],pairs[1]);
                urlFileMap.put(pairs[1],pairs[0]);
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
        String filePath = "/Users/jessiekuo/Desktop/CSCI572_HW4/solr-7.7.0/EdgeLists/reutersnews";
        File files = new File(filePath);


        Set<String> edges = new HashSet<>();
        System.out.println("start calculating");
        for(File file: Objects.requireNonNull(files.listFiles())) {
        	if (fileUrlMap.get(file.getName()) != null) {
	            Document doc = Jsoup.parse(file,"UTF-8",fileUrlMap.get(file.getName()));
	            Elements links = doc.select("a[href]");
	
	            for (Element link:links) {
	                String url = link.attr("href").trim();
	                if(urlFileMap.containsKey(url)) {
	                    edges.add(file.getName() + " " + urlFileMap.get(url));
	                }
	            }
	         
        	}
        }
        System.out.println("start printing");
        for (String s:edges) {
            writer.println(s);
        }
        writer.flush();
        writer.close();
        System.out.println("finished");

    }
    private static void print(String msg, Object... args) {
        System.out.println(String.format(msg,args));
    }
    private static String trim(String s, int width) {
        if (s.length() > width) {
            return s.substring(0,width-1) + ".";
        } else {
            return s;
        }
    }

//		Map<String, String> fileUrlMap = new HashMap<String, String>();
//        Map<String, String> urlFileMap = new HashMap<String, String>();
//        
//		BufferedReader br = new BufferedReader(new FileReader("/Users/jessiekuo/Desktop/CSCI572_HW4/solr-7.7.0/EdgeLists/URLtoHTML_reuters_news.csv"));
//		
//		String line = "";
//        while ((line = br.readLine()) != null) {
//            String[] fileUrl = line.split(",");
//            fileUrlMap.put(fileUrl[0], fileUrl[1]);
//            urlFileMap.put(fileUrl[1], fileUrl[0]);
//        }
//        br.close();
//        
//        String dirPath = "/Users/jessiekuo/Desktop/CSCI572_HW4/solr-7.7.0/EdgeLists/";
//		File dir = new File(dirPath);
//		Set<String> edges = new HashSet<String>();
//		for(File file: dir.listFiles()){
//			Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
//			Elements links = doc.select("a[href]");
//						
//			for(Element link: links){
//				String url = link.attr("abs:href").trim();
//				if(urlFileMap.containsKey(url)) {
//					edges.add(file.getName() + " " + urlFileMap.get(url));
//				}
//			}
//		}
//		
//		PrintWriter writer = new PrintWriter(new FileWriter("edgeList.txt")); 
//		for(String s: edges){
//			writer.println(s);
//		}
//		writer.close();

	//}

}
