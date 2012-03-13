<?php
/**
 * 生成点击热图
 * @author clicki http://www.clicki.cn
 */
class HeatMap {

	const MAP_SIZE = 0.2;
	const OPACITY = 10;

	/**
	 * 生成热力图函数
	 * @params $width 热力图宽
	 * @params $height 热力图高
	 * @params $clicks 点击坐标数组
	 */
	public function genHeatMap($file, $width = 100, $height = 100, $clicks){
		$pngFile = $file;
		if($clicks){
			$width = $width + 64;
			$height = $height + 64;

			$pointerImage = "./images/pointer.png";
			$opacity0 = @MagickGetQuantumRange();
			$opacity100 = 0;

			//gather the actual opacity number
			$opacity = $opacity0 - ($opacity0 * self::OPACITY/100 ) ;
			//echo("/*$opacity*/");
			//initialize the wands
			$heatmapWand = NewMagickWand();
			$pointerWand = NewMagickWand();
			$pointerxWand = NewMagickWand();

			//read in the images

			MagickReadImage($pointerWand, $pointerImage);
			MagickResizeImage($pointerWand, 64*self::MAP_SIZE,64*self::MAP_SIZE, MW_QuadraticFilter, 1);

			MagickNewImage($heatmapWand,$width*self::MAP_SIZE,$height*self::MAP_SIZE,'black');//TODO: 改为透明的
			//$heatmapWand = imagecreatetruecolor(($width-$this->left)*self::MAP_SIZE, ($height-$this->top)*self::MAP_SIZE);
			MagickSetFormat($heatmapWand,"png");
			//setting the pointer index
			MagickSetImageIndex($pointerWand, 0);
			MagickSetImageType($pointerWand, MW_TrueColorMatteType);
			//MagickEvaluateImage($heatmapWand, MW_SubtractEvaluateOperator, 1000, MW_OpacityChannel);				
			//seting the opacity level
			MagickEvaluateImage($pointerWand, MW_SubtractEvaluateOperator, $opacity, MW_OpacityChannel);

			$c = count($clicks);
			for($i = 0; $i<$c; $i++){
				$x = $clicks[$i]['x']+32;
				$y = $clicks[$i]['y']+32;
				//echo ($x);
				//MagickCompositeImage($heatmapWand, $pointerWand, MW_ScreenCompositeOp, ($x-$this->left)*$map_size, ($y-$this->top)*$map_size);
				MagickCompositeImage($heatmapWand, $pointerWand, MW_ScreenCompositeOp, $x*self::MAP_SIZE, $y*self::MAP_SIZE);
			}

			MagickBlurImage($heatmapWand,0, 1);
			MagickWriteImage($heatmapWand, $pngFile);
			system("/usr/local/bin/convert $pngFile -type TruecolorMatte ./images/colors.png -fx \"v.p{0,u*v.h}\" $pngFile");
		}
	}
}

//点击数组x y
$clicks = array();

//生成测试坐标
for ($i = 0; $i < 100; $i ++) {
	$clicks[] = array('x' => $i, 'y' => $i + 1);
}

$heatMap = new HeatMap();
$heatMap->genHeatMap(1024, 768, $clicks);
